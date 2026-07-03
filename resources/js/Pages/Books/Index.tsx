import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Book, BookPayload, useLibrary } from '@/contexts/LibraryContext';
import { Head } from '@inertiajs/react';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import DeleteOutlineIcon from '@mui/icons-material/DeleteOutline';
import EditIcon from '@mui/icons-material/Edit';
import ShoppingCartCheckoutIcon from '@mui/icons-material/ShoppingCartCheckout';
import {
    Alert,
    Box,
    Button,
    Chip,
    CircularProgress,
    Dialog,
    DialogActions,
    DialogContent,
    DialogTitle,
    FormControlLabel,
    Paper,
    Snackbar,
    Stack,
    Switch,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    TextField,
    Typography,
} from '@mui/material';
import { useEffect, useMemo, useState } from 'react';

const emptyForm: BookPayload = {
    title: '',
    author_name: '',
    isbn_code: '',
    total_quantity: 1,
    active: true,
};

export default function Index(): JSX.Element {
    const {
        books,
        loadingBooks,
        fetchBooks,
        createBook,
        updateBook,
        deleteBook,
        borrowBook,
        error,
        clearError,
    } = useLibrary();

    const [form, setForm] = useState<BookPayload>(emptyForm);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [editingBook, setEditingBook] = useState<Book | null>(null);
    const [saving, setSaving] = useState(false);
    const [feedback, setFeedback] = useState<string | null>(null);

    useEffect(() => {
        fetchBooks();
    }, [fetchBooks]);

    const dialogTitle = useMemo(
        () => (editingBook ? 'Editar livro' : 'Novo livro'),
        [editingBook],
    );

    const handleOpenDialog = (book?: Book): void => {
        if (book) {
            setEditingBook(book);
            setForm({
                title: book.title,
                author_name: book.author,
                isbn_code: book.isbn_code,
                total_quantity: book.total_quantity,
                active: book.active,
            });
        } else {
            setEditingBook(null);
            setForm(emptyForm);
        }

        setDialogOpen(true);
    };

    const handleCloseDialog = (): void => {
        setDialogOpen(false);
        setForm(emptyForm);
        setEditingBook(null);
    };

    const handleSubmit = async (): Promise<void> => {
        setSaving(true);
        try {
            if (editingBook) {
                await updateBook(editingBook.id, form);
                setFeedback('Livro atualizado com sucesso.');
            } else {
                await createBook(form);
                setFeedback('Livro criado com sucesso.');
            }

            handleCloseDialog();
        } finally {
            setSaving(false);
        }
    };

    const handleBorrow = async (bookId: number): Promise<void> => {
        setSaving(true);
        try {
            const identifier = await borrowBook(bookId);
            setFeedback(`Empréstimo criado. Identificador: ${identifier}`);
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (bookId: number): Promise<void> => {
        setSaving(true);
        try {
            await deleteBook(bookId);
            setFeedback('Livro removido com sucesso.');
        } finally {
            setSaving(false);
        }
    };

    const handleFieldChange = (field: keyof BookPayload, value: string | number | boolean): void => {
        setForm((previous) => ({
            ...previous,
            [field]: value,
        }));
    };

    const renderStatusChip = (book: Book): JSX.Element => {
        const hasStock = book.available_quantity > 0 && book.active;
        const label = book.active ? `${book.available_quantity} disponíveis` : 'Inativo';

        return (
            <Chip
                label={label}
                color={hasStock ? 'success' : 'default'}
                variant={hasStock ? 'filled' : 'outlined'}
                size="small"
            />
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <Stack direction="row" justifyContent="space-between" alignItems="center">
                    <Box>
                        <Typography variant="caption" sx={{ letterSpacing: 2, textTransform: 'uppercase' }}>
                            Biblioteca
                        </Typography>
                        <Typography variant="h5" fontWeight={600} color="text.primary">
                            Catálogo de livros
                        </Typography>
                    </Box>

                    <Stack direction="row" spacing={1}>
                        <Button
                            variant="outlined"
                            onClick={() => fetchBooks(true)}
                            disabled={loadingBooks}
                        >
                            Recarregar
                        </Button>
                        <Button
                            variant="contained"
                            startIcon={<AddCircleOutlineIcon />}
                            onClick={() => handleOpenDialog()}
                        >
                            Novo livro
                        </Button>
                    </Stack>
                </Stack>
            }
        >
            <Head title="Livros" />

            <Box sx={{ py: 6 }}>
                <TableContainer component={Paper} elevation={0} sx={{ borderRadius: 2 }}>
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell>Título</TableCell>
                                <TableCell>Autor</TableCell>
                                <TableCell>ISBN</TableCell>
                                <TableCell align="right">Disponíveis</TableCell>
                                <TableCell align="right">Emprestados</TableCell>
                                <TableCell align="right">Total</TableCell>
                                <TableCell align="right">Ações</TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {loadingBooks && (
                                <TableRow>
                                    <TableCell colSpan={7} align="center">
                                        <CircularProgress size={24} />
                                    </TableCell>
                                </TableRow>
                            )}

                            {!loadingBooks && books?.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7} align="center">
                                        Nenhum livro cadastrado.
                                    </TableCell>
                                </TableRow>
                            )}

                            {!loadingBooks && books?.map((book) => (
                                <TableRow key={book.id} hover>
                                    <TableCell>{book.title}</TableCell>
                                    <TableCell>{book.author}</TableCell>
                                    <TableCell>{book.isbn_code}</TableCell>
                                    <TableCell align="right">{renderStatusChip(book)}</TableCell>
                                    <TableCell align="right">{book.borrowed_quantity}</TableCell>
                                    <TableCell align="right">{book.total_quantity}</TableCell>
                                    <TableCell align="right">
                                        <Stack direction="row" spacing={1} justifyContent="flex-end">
                                            <Button
                                                size="small"
                                                variant="outlined"
                                                startIcon={<ShoppingCartCheckoutIcon />}
                                                disabled={book.available_quantity <= 0 || saving}
                                                onClick={() => handleBorrow(book.id)}
                                            >
                                                Alugar
                                            </Button>
                                            <Button
                                                size="small"
                                                variant="text"
                                                startIcon={<EditIcon />}
                                                onClick={() => handleOpenDialog(book)}
                                            >
                                                Editar
                                            </Button>
                                            <Button
                                                size="small"
                                                variant="text"
                                                color="error"
                                                startIcon={<DeleteOutlineIcon />}
                                                onClick={() => handleDelete(book.id)}
                                            >
                                                Remover
                                            </Button>
                                        </Stack>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </TableContainer>
            </Box>

            <Dialog open={dialogOpen} onClose={handleCloseDialog} fullWidth maxWidth="sm">
                <DialogTitle>{dialogTitle}</DialogTitle>
                <DialogContent>
                    <Stack spacing={2} sx={{ mt: 1 }}>
                        <TextField
                            label="Título"
                            value={form.title}
                            onChange={(event) => handleFieldChange('title', event.target.value)}
                            required
                            fullWidth
                        />
                        <TextField
                            label="Autor"
                            value={form.author_name}
                            onChange={(event) => handleFieldChange('author_name', event.target.value)}
                            required
                            fullWidth
                        />
                        <TextField
                            label="ISBN (13 dígitos)"
                            value={form.isbn_code}
                            onChange={(event) => handleFieldChange('isbn_code', event.target.value)}
                            required
                            inputProps={{ maxLength: 13 }}
                            fullWidth
                        />
                        <TextField
                            label="Quantidade total"
                            type="number"
                            inputProps={{ min: 1 }}
                            value={form.total_quantity}
                            onChange={(event) =>
                                handleFieldChange('total_quantity', Number(event.target.value))
                            }
                            required
                            fullWidth
                        />
                        <FormControlLabel
                            control={
                                <Switch
                                    checked={form.active ?? true}
                                    onChange={(event) => handleFieldChange('active', event.target.checked)}
                                />
                            }
                            label="Livro ativo para empréstimo"
                        />
                    </Stack>
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleCloseDialog}>Cancelar</Button>
                    <Button onClick={handleSubmit} variant="contained" disabled={saving}>
                        {saving ? 'Salvando...' : 'Salvar'}
                    </Button>
                </DialogActions>
            </Dialog>

            <Snackbar
                open={Boolean(feedback || error)}
                autoHideDuration={6000}
                onClose={() => {
                    setFeedback(null);
                    clearError();
                }}
            >
                <Alert
                    severity={error ? 'error' : 'success'}
                    onClose={() => {
                        setFeedback(null);
                        clearError();
                    }}
                    variant="filled"
                >
                    {error ?? feedback}
                </Alert>
            </Snackbar>
        </AuthenticatedLayout>
    );
}
