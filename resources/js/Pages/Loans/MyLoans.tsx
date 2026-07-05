import PaginationComponent from '@/Components/Pagination';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Loan, useLibrary } from '@/contexts/LibraryContext';
import { Head } from '@inertiajs/react';
import AssignmentTurnedInIcon from '@mui/icons-material/AssignmentTurnedInOutlined';
import ReplayIcon from '@mui/icons-material/ReplayOutlined';
import {
    Alert,
    Box,
    Button,
    Chip,
    CircularProgress,
    Paper,
    Snackbar,
    Stack,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    TextField,
    Typography,
} from '@mui/material';
import { useEffect, useState } from 'react';

const formatDate = (value: string | null): string => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString();
};

const loanStatusChip = (loan: Loan): JSX.Element => {
    if (loan.ended_at) {
        return <Chip label="Devolvido" color="default" size="small" />;
    }

    if (loan.is_overdue) {
        return <Chip label="Atrasado" color="error" size="small" />;
    }

    return <Chip label="Em andamento" color="primary" size="small" />;
};

export default function MyLoans(): JSX.Element {
    const {
        loans,
        loansPagination,
        loadingLoans,
        fetchLoans,
        returnLoan,
        returnByIdentifier,
        error,
        clearError,
    } = useLibrary();

    const [feedback, setFeedback] = useState<string | null>(null);
    const [identifier, setIdentifier] = useState('');
    const [processing, setProcessing] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);

    useEffect(() => {
        fetchLoans(false, currentPage);
    }, [fetchLoans, currentPage]);

    const handlePageChange = (page: number): void => {
        setCurrentPage(page);
    };

    const handleReturn = async (loanId: number): Promise<void> => {
        setProcessing(true);
        try {
            await returnLoan(loanId);
            setFeedback('Livro devolvido com sucesso.');
        } finally {
            setProcessing(false);
        }
    };

    const handleReturnByIdentifier = async (): Promise<void> => {
        if (!identifier.trim()) {
            return;
        }

        setProcessing(true);
        try {
            await returnByIdentifier(identifier.trim());
            setFeedback('Todos os livros deste empréstimo foram devolvidos.');
            setIdentifier('');
        } finally {
            setProcessing(false);
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <Stack direction="row" sx={{ justifyContent: 'space-between', alignItems: 'center' }}>
                    <Box>
                        <Typography variant="caption" sx={{ letterSpacing: 2, textTransform: 'uppercase', color: '#4b5563' }}>
                            Meus empréstimos
                        </Typography>
                        <Typography variant="h5" sx={{ fontWeight: 600, color: '#111827' }}>
                            Controle de empréstimos
                        </Typography>
                    </Box>

                    <Button
                        variant="outlined"
                        startIcon={<ReplayIcon />}
                        onClick={() => fetchLoans(true)}
                        disabled={loadingLoans}
                        sx={{ color: '#111827', borderColor: '#d1d5db' }}
                    >
                        Atualizar
                    </Button>
                </Stack>
            }
        >
            <Head title="Meus Empréstimos" />

            <Box sx={{ py: 6, backgroundColor: '#f3f4f6', minHeight: '100vh' }}>
                <Stack spacing={3}>
                    <Paper sx={{ p: 2, borderRadius: 2, backgroundColor: '#ffffff' }} variant="outlined">
                        <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2} sx={{ alignItems: 'center' }}>
                            <TextField
                                label="Identificador de empréstimo"
                                value={identifier}
                                onChange={(event) => setIdentifier(event.target.value)}
                                fullWidth
                            />
                            <Button
                                variant="contained"
                                startIcon={<AssignmentTurnedInIcon />}
                                onClick={handleReturnByIdentifier}
                                disabled={processing || !identifier.trim()}
                                sx={{ backgroundColor: '#2563eb', color: '#ffffff', '&:hover': { backgroundColor: '#1d4ed8' } }}
                            >
                                Devolver todos
                            </Button>
                        </Stack>
                    </Paper>

                <TableContainer component={Paper} variant="outlined" sx={{ borderRadius: 2, backgroundColor: '#ffffff' }}>
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell>Título</TableCell>
                                <TableCell>Autor</TableCell>
                                <TableCell>Identificador</TableCell>
                                <TableCell>Início</TableCell>
                                <TableCell>Previsão de devolução</TableCell>
                                <TableCell>Status</TableCell>
                                <TableCell align="right">Ações</TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {loadingLoans && (
                                <TableRow>
                                    <TableCell colSpan={7} align="center">
                                        <CircularProgress size={24} />
                                    </TableCell>
                                </TableRow>
                            )}

                            {!loadingLoans && (!loans || loans.length === 0) && (
                                <TableRow>
                                    <TableCell colSpan={7} align="center">
                                        Nenhum empréstimo encontrado.
                                    </TableCell>
                                </TableRow>
                            )}

                            {!loadingLoans && loans?.map((loan) => (
                                <TableRow key={loan.id} hover>
                                    <TableCell>{loan.title}</TableCell>
                                    <TableCell>{loan.author}</TableCell>
                                    <TableCell>{loan.identifier}</TableCell>
                                    <TableCell>{formatDate(loan.started_at)}</TableCell>
                                    <TableCell>{formatDate(loan.predicted_end_at)}</TableCell>
                                    <TableCell>{loanStatusChip(loan)}</TableCell>
                                    <TableCell align="right">
                                        {!loan.ended_at && (
                                            <Button
                                                size="small"
                                                variant="outlined"
                                                onClick={() => handleReturn(loan.id)}
                                                disabled={processing}
                                                sx={{ color: '#2563eb', borderColor: '#2563eb', '&:hover': { backgroundColor: '#eff6ff' } }}
                                            >
                                                Devolver
                                            </Button>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </TableContainer>

                {loansPagination && (
                    <Box sx={{ display: 'flex', justifyContent: 'center', mt: 3 }}>
                        <PaginationComponent
                            currentPage={loansPagination.currentPage}
                            totalPages={loansPagination.totalPages}
                            onPageChange={handlePageChange}
                        />
                    </Box>
                )}
            </Stack>
            </Box>

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
