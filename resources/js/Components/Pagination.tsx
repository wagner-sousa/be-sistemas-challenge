import { Pagination as MuiPagination } from '@mui/material';

type PaginationProps = {
    currentPage: number;
    totalPages: number;
    onPageChange: (page: number) => void;
};

export default function PaginationComponent({
    currentPage,
    totalPages,
    onPageChange,
}: PaginationProps): JSX.Element {
    if (totalPages <= 1) {
        return <></>;
    }

    return (
        <MuiPagination
            count={totalPages}
            page={currentPage}
            onChange={(_event, page) => onPageChange(page)}
            color="primary"
            showFirstButton
            showLastButton
        />
    );
}
