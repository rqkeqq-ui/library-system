/**
 * JavaScript для страницы управления
 */

// Открыть модальное окно выдачи книги
async function openIssueModal(readerId, readerName) {
    document.getElementById('issueReaderId').value = readerId;
    document.getElementById('issueReaderName').textContent = readerName;

    // Загрузить доступные книги
    try {
        const response = await fetch('api/get-available-books.php');
        const data = await response.json();

        if (data.success) {
            const grid = document.getElementById('availableBooksGrid');
            grid.innerHTML = data.books.map(book => `
                <div class="book-card" onclick="issueBookToReader(${readerId}, ${book.id})">
                    <div class="book-cover">
                        <div class="book-cover-placeholder">📕</div>
                    </div>
                    <div class="book-info">
                        <h4 class="book-title" style="font-size: 14px;">${escapeHtml(book.title)}</h4>
                        <p class="book-author" style="font-size: 12px;">${escapeHtml(book.author)}</p>
                        <p class="book-genre" style="font-size: 11px;">${escapeHtml(book.genre)}</p>
                    </div>
                </div>
            `).join('');

            if (data.books.length === 0) {
                grid.innerHTML = '<p style="text-align: center; color: #6B7280;">Нет доступных книг</p>';
            }
        }

        openModal('issueModal');
    } catch (error) {
        console.error('Error:', error);
        showNotification('Ошибка при загрузке книг', 'error');
    }
}

// Выдать книгу читателю
async function issueBookToReader(readerId, bookId) {
    try {
        const formData = new FormData();
        formData.append('reader_id', readerId);
        formData.append('book_id', bookId);

        const response = await fetch('api/issue-book.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message);
            closeModal('issueModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Ошибка при выдаче книги', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Ошибка при выдаче книги', 'error');
    }
}

// Открыть модальное окно продления срока
function openExtendModal(loanId, bookId, bookTitle, readerName) {
    document.getElementById('extendLoanId').value = loanId;
    document.getElementById('extendBookTitle').textContent = bookTitle;
    document.getElementById('extendReaderName').textContent = readerName;

    openModal('extendModal');
}

// Обработчик формы продления срока
document.addEventListener('DOMContentLoaded', () => {
    const extendForm = document.getElementById('extendForm');
    if (extendForm) {
        extendForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const loanId = document.getElementById('extendLoanId').value;
            const days = document.getElementById('extendDays').value;

            const formData = new FormData();
            formData.append('loan_id', loanId);
            formData.append('days', days);

            try {
                const response = await fetch('api/extend-loan.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message);
                    closeModal('extendModal');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.error || 'Ошибка при продлении срока', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка при продлении срока', 'error');
            }
        });
    }
});

// Открыть модальное окно принятия книги
function openReturnModal(loanId, bookId, bookTitle, readerName) {
    document.getElementById('returnLoanId').value = loanId;
    document.getElementById('returnBookId').value = bookId;
    document.getElementById('returnBookTitle').textContent = bookTitle;
    document.getElementById('returnReaderName').textContent = readerName;

    openModal('returnModal');
}

// Обработчик формы возврата книги
document.addEventListener('DOMContentLoaded', () => {
    const returnForm = document.getElementById('returnForm');
    if (returnForm) {
        returnForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const loanId = document.getElementById('returnLoanId').value;
            const bookId = document.getElementById('returnBookId').value;

            const formData = new FormData();
            formData.append('loan_id', loanId);
            formData.append('book_id', bookId);

            try {
                const response = await fetch('api/return-book.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message);
                    closeModal('returnModal');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.error || 'Ошибка при возврате книги', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка при возврате книги', 'error');
            }
        });
    }
});

// Одобрить заявку
async function approveRequest(requestId, readerId, bookId) {
    if (!confirm('Одобрить заявку и выдать книгу?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('action', 'approve');

        const response = await fetch('api/manage-request.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Ошибка при одобрении заявки', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Ошибка при одобрении заявки', 'error');
    }
}

// Отклонить заявку
async function rejectRequest(requestId) {
    if (!confirm('Отклонить заявку?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('action', 'reject');

        const response = await fetch('api/manage-request.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Ошибка при отклонении заявки', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Ошибка при отклонении заявки', 'error');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
