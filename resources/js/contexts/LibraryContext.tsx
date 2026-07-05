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
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

type PaginationMeta = {
    currentPage: number;
    totalPages: number;
    perPage: number;
    total: number;
};

type LibraryContextValue = {
    books: Book[] | null;
    loans: Loan[] | null;
    booksPagination: PaginationMeta | null;
    loansPagination: PaginationMeta | null;
    loadingBooks: boolean;
    loadingLoans: boolean;
    error: string | null;
    fetchBooks: (force?: boolean, page?: number) => Promise<Book[]>;
    fetchLoans: (force?: boolean, page?: number) => Promise<Loan[]>;
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
    const [booksPagination, setBooksPagination] = useState<PaginationMeta | null>(null);
    const [loansPagination, setLoansPagination] = useState<PaginationMeta | null>(null);
    const [loadingBooks, setLoadingBooks] = useState(false);
    const [loadingLoans, setLoadingLoans] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const parsePaginated = <T,>(payload: Paginated<T> | T[]): { data: T[]; pagination?: PaginationMeta } => {
        if (Array.isArray(payload)) {
            return { data: payload as T[] };
        }

        if (typeof payload === 'object' && payload !== null && 'data' in payload) {
            const paginated = payload as Paginated<T>;
            return {
                data: paginated.data,
                pagination: {
                    currentPage: paginated.current_page,
                    totalPages: paginated.last_page,
                    perPage: paginated.per_page,
                    total: paginated.total,
                },
            };
        }

        return { data: [] };
    };

    const handleError = (exception: unknown): never => {
        const message = exception instanceof Error ? exception.message : 'Erro inesperado.';
        setError(message);
        throw exception instanceof Error ? exception : new Error(message);
    };

    const fetchBooks = useCallback(async (force = false, page = 1): Promise<Book[]> => {
        if (books && !force && page === booksPagination?.currentPage) {
            return books;
        }

        setLoadingBooks(true);

        try {
            const response = await apiFetch<Paginated<Book>>(`/api/books?page=${page}`);
            const { data, pagination } = parsePaginated<Book>(response);
            setBooks(data);
            if (pagination) {
                setBooksPagination(pagination);
            }
            setError(null);
            return data;
        } catch (exception) {
            return handleError(exception);
        } finally {
            setLoadingBooks(false);
        }
    }, [books, booksPagination]);

    const fetchLoans = useCallback(async (force = false, page = 1): Promise<Loan[]> => {
        if (loans && !force && page === loansPagination?.currentPage) {
            return loans;
        }

        setLoadingLoans(true);

        try {
            const response = await apiFetch<Paginated<Loan>>(`/api/borrowed-books?page=${page}`);
            const { data, pagination } = parsePaginated<Loan>(response);
            setLoans(data);
            if (pagination) {
                setLoansPagination(pagination);
            }
            setError(null);
            return data;
        } catch (exception) {
            return handleError(exception);
        } finally {
            setLoadingLoans(false);
        }
    }, [loans, loansPagination]);

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
            booksPagination,
            loansPagination,
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
            booksPagination,
            loansPagination,
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
