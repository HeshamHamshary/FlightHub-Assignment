import { type PaginationMeta } from '../services/flightApi'

interface PaginationProps {
  meta: PaginationMeta | null
  onPageChange: (page: number) => void
}

function Pagination({ meta, onPageChange }: PaginationProps) {
  if (!meta || meta.totalPages <= 1) {
    return null
  }

  const { page, totalPages, hasPreviousPage, hasNextPage, total, perPage } = meta

  // Calculate the range of results being shown
  const startResult = (page - 1) * perPage + 1
  const endResult = Math.min(page * perPage, total)

  return (
    <div className="pagination-container">
      {/* Results info */}
      <div className="pagination-info">
        Showing {startResult}-{endResult} of {total} results
      </div>

      {/* Pagination controls */}
      <div className="pagination-controls">
        {/* Previous button */}
        <button
          className={`pagination-btn ${!hasPreviousPage ? 'disabled' : ''}`}
          onClick={() => hasPreviousPage && onPageChange(page - 1)}
          disabled={!hasPreviousPage}
        >
          ← Previous
        </button>

        {/* Page numbers - show 5 pages around current */}
        <div className="page-numbers">
          {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
            const startPage = Math.max(1, Math.min(page - 2, totalPages - 4))
            const pageNum = startPage + i
            
            return (
              <button
                key={pageNum}
                className={`pagination-btn page-number ${pageNum === page ? 'active' : ''}`}
                onClick={() => onPageChange(pageNum)}
              >
                {pageNum}
              </button>
            )
          })}
        </div>

        {/* Next button */}
        <button
          className={`pagination-btn ${!hasNextPage ? 'disabled' : ''}`}
          onClick={() => hasNextPage && onPageChange(page + 1)}
          disabled={!hasNextPage}
        >
          Next →
        </button>
      </div>
    </div>
  )
}

export default Pagination
