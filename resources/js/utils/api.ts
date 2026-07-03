export class ApiError extends Error {
    status?: number;

    constructor(message: string, status?: number) {
        super(message);
        this.status = status;
    }
}

const getCsrfToken = (): string | null => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    return token ?? null;
};

export async function apiFetch<T>(input: RequestInfo, init: RequestInit = {}): Promise<T> {
    const csrfToken = getCsrfToken();

    const headers = new Headers(init.headers ?? {});
    if (!headers.has('Content-Type') && init.body && !(init.body instanceof FormData)) {
        headers.set('Content-Type', 'application/json');
    }

    if (csrfToken && !headers.has('X-CSRF-TOKEN')) {
        headers.set('X-CSRF-TOKEN', csrfToken);
    }

    const response = await fetch(input, {
        credentials: 'same-origin',
        ...init,
        headers,
    });

    if (!response.ok) {
        let message = response.statusText;

        try {
            const payload = await response.json();
            message = payload.message ?? message;
        } catch (error) {
            // ignore parsing errors and fall back to status text
        }

        throw new ApiError(message, response.status);
    }

    if (response.status === 204) {
        return undefined as T;
    }

    return (await response.json()) as T;
}
