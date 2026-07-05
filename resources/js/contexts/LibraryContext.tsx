import { apiFetch, ApiError } from '@/utils/api';
import {
    PropsWithChildren,
    createContext,
    useCallback,
    useContext,
    useMemo,
    useState,
} from 'react';

export type BookPayload = {
    title: string;
    author_name: string;
    isbn_code: string;
    total_quantity: number;
    active?: boolean;
};

export type Book = {
    id: number;
    title: string;
    author_id: number;
    author: string;
    isbn_code: string;
    total_quantity: number;
    borrowed_quantity: number;
    available_quantity: number;
    active: boolean;
};

export type Loan = {
    id: number;
    identifier: string;
    book_id: number;
    title: string;
    author: string;
    isbn_code: string;
    started_at: string;
    ended_at: string | null;
    predicted_end_at: string;
    is_overdue: boolean;
    total_quantity: number;
    borrowed_quantity: number;
    active: boolean;
};

type Paginated<T> = {
    data: T[];
};

type LibraryContextValue = {
    books: Book[] | null;
    loans: Loan[] | null;
    loadingBooks: boolean;
    loadingLoans: boolean;
    error: string | null;
    fetchBooks: (force?: boolean) => Promise<Book[]>;
    fetchLoans: (force?: boolean) => Promise<Loan[]>;
    createBook: (payload: BookPayload) => Promise<Book>;
    updateBook: (id: number, payload: BookPayload) => Promise<Book>;
    deleteBook: (id: number) => Promise<void>;
    borrowBook: (bookId: number) => Promise<string>;
    returnLoan: (loanId: number) => Promise<void>;
    returnByIdentifier: (identifier: string) => Promise<void>;
    clearError: () => void;
};

const LibraryContext = createContext<LibraryContextValue | null>(null);

export function LibraryProvider({ children }: PropsWithChildren): JSX.Element {
    const [books, setBooks] = useState<Book[] | null>(null);
    const [loans, setLoans] = useState<Loan[] | null>(null);
    const [loadingBooks, setLoadingBooks] = useState(false);
    const [loadingLoans, setLoadingLoans] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const parsePaginated = <T,>(payload: Paginated<T> | T[]): T[] => {
        if (Array.isArray(payload)) {
            return payload as T[];
        }

        if (typeof payload === 'object' && payload !== null && 'data' in payload) {
            return (payload as Paginated<T>).data;
        }

        return [];
    };

    const handleError = (exception: unknown): never => {
        const message = exception instanceof Error ? exception.message : 'Erro inesperado.';
        setError(message);
        throw exception instanceof Error ? exception : new Error(message);
    };

    const fetchBooks = useCallback(async (force = false): Promise<Book[]> => {
        if (books && !force) {
            return books;
        }

        setLoadingBooks(true);

        try {
            const response = await apiFetch<Paginated<Book>>('/api/books');
            const data = parsePaginated<Book>(response);
            setBooks(data);
            setError(null);
            return data;
        } catch (exception) {
            return handleError(exception);
        } finally {
            setLoadingBooks(false);
        }
    }, [books]);

    const fetchLoans = useCallback(async (force = false): Promise<Loan[]> => {
        if (loans && !force) {
            return loans;
        }

        setLoadingLoans(true);

        try {
            const response = await apiFetch<{ data: Loan[] }>('/api/borrowed-books');
            setLoans(response.data);
            setError(null);
            return response.data;
        } catch (exception) {
            return handleError(exception);
        } finally {
            setLoadingLoans(false);
        }
    }, [loans]);

    const createBook = useCallback(async (payload: BookPayload): Promise<Book> => {
        try {
            const book = await apiFetch<Book>('/api/books', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            setBooks((previous) => (previous ? [book, ...previous] : [book]));
            setError(null);

            return book;
        } catch (exception) {
            return handleError(exception);
        }
    }, []);

    const updateBook = useCallback(async (id: number, payload: BookPayload): Promise<Book> => {
        try {
            const book = await apiFetch<Book>(`/api/books/${id}`, {
                method: 'PUT',
                body: JSON.stringify(payload),
            });

            setBooks((previous) =>
                previous?.map((current) => (current.id === id ? book : current)) ?? [book],
            );
            setError(null);

            return book;
        } catch (exception) {
            return handleError(exception);
        }
    }, []);

    const deleteBook = useCallback(async (id: number): Promise<void> => {
        try {
            await apiFetch<void>(`/api/books/${id}`, {
                method: 'DELETE',
            });

            setBooks((previous) => previous?.filter((book) => book.id !== id) ?? null);
            setError(null);
        } catch (exception) {
            handleError(exception);
        }
    }, []);

    const borrowBook = useCallback(async (bookId: number): Promise<string> => {
        try {
            const response = await apiFetch<{ identifier: string }>('/api/borrowed-books', {
                method: 'POST',
                body: JSON.stringify({ books: [bookId] }),
            });

            await fetchBooks(true);
            await fetchLoans(true);
            setError(null);

            return response.identifier;
        } catch (exception) {
            return handleError(exception);
        }
    }, [fetchBooks, fetchLoans]);

    const returnLoan = useCallback(async (loanId: number): Promise<void> => {
        try {
            await apiFetch(`/api/borrowed-books/return/book/${loanId}`, {
                method: 'PATCH',
            });

            setLoans((previous) =>
                previous?.map((loan) =>
                    loan.id === loanId
                        ? {
                              ...loan,
                              ended_at: new Date().toISOString(),
                              is_overdue: false,
                          }
                        : loan,
                ) ?? null,
            );

            await fetchBooks(true);
            setError(null);
        } catch (exception) {
            handleError(exception);
        }
    }, [fetchBooks]);

    const returnByIdentifier = useCallback(async (identifier: string): Promise<void> => {
        try {
            await apiFetch(`/api/borrowed-books/return/${identifier}`, {
                method: 'POST',
            });

            await Promise.all([fetchBooks(true), fetchLoans(true)]);
            setError(null);
        } catch (exception) {
            handleError(exception);
        }
    }, [fetchBooks, fetchLoans]);

    const clearError = useCallback((): void => {
        setError(null);
    }, []);

    const value = useMemo<LibraryContextValue>(
        () => ({
            books,
            loans,
            loadingBooks,
            loadingLoans,
            error,
            fetchBooks,
            fetchLoans,
            createBook,
            updateBook,
            deleteBook,
            borrowBook,
            returnLoan,
            returnByIdentifier,
            clearError,
        }),
        [
            books,
            loans,
            loadingBooks,
            loadingLoans,
            error,
            fetchBooks,
            fetchLoans,
            createBook,
            updateBook,
            deleteBook,
            borrowBook,
            returnLoan,
            returnByIdentifier,
            clearError,
        ],
    );

    return <LibraryContext.Provider value={value}>{children}</LibraryContext.Provider>;
}

export function useLibrary(): LibraryContextValue {
    const context = useContext(LibraryContext);

    if (!context) {
        throw new ApiError('useLibrary deve ser usado dentro de LibraryProvider.');
    }

    return context;
}
