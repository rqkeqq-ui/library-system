/**
 * JavaScript для работы с книгами
 */

// Открыть модальное окно с информацией о книге
async function openBookModal(bookId) {
    try {
        const response = await fetch(`api/get-book.php?id=${bookId}`);
        const data = await response.json();

        if (!data.success) {
            showNotification(data.error || 'Ошибка загрузки книги', 'error');
            return;
        }

        const book = data.book;
        const modalContent = document.getElementById('bookModalContent');

        modalContent.innerHTML = `
            <div class="book-modal-info">
                <span class="book-icon-large">📕</span>
                <div>
                    <h2 style="margin: 0;">${escapeHtml(book.title)}</h2>
                    <p style="margin: 4px 0; color: #6B7280;">${escapeHtml(book.author)}</p>
                </div>
            </div>

            <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                <span class="badge ${book.status === 'available' ? 'badge-success' : 'badge-gray'}">
                    ${book.status === 'available' ? 'Доступна' : 'Выдана'}
                </span>
                <span class="badge badge-gray">${escapeHtml(book.genre)}</span>
            </div>

            <p style="margin-bottom: 8px;"><strong>ISBN:</strong> ${escapeHtml(book.isbn)}</p>

            ${book.description ? `<p style="color: #6B7280;">${escapeHtml(book.description)}</p>` : ''}

            ${data.canRequest && !data.hasActiveRequest ? `
                <button class="btn btn-primary btn-block" onclick="requestBook(${book.id})">
                    📋 Взять книгу
                </button>
            ` : ''}

            ${data.hasActiveRequest ? `
                <div class="info-box">
                    ✓ Заявка на эту книгу уже отправлена<br>
                    <a href="my-requests.php" style="color: #3B82F6; text-decoration: underline;">
                        Посмотреть мои заявки →
                    </a>
                </div>
            ` : ''}
        `;

        openModal('bookModal');
    } catch (error) {
        console.error('Error:', error);
        showNotification('Ошибка при загрузке информации о книге', 'error');
    }
}

// Подать заявку на книгу
async function requestBook(bookId) {
    try {
        const formData = new FormData();
        formData.append('book_id', bookId);
        appendCsrfToken(formData);

        const response = await fetch('api/request-book.php', {
            method: 'POST',
            headers: csrfHeaders(),
            body: formData,
            credentials: 'same-origin'
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message + ' Перенаправление на страницу заявок...');
            closeModal('bookModal');
            // Перенаправить на страницу "Мои заявки" через 1.5 секунды
            setTimeout(() => {
                window.location.href = 'my-requests.php';
            }, 1500);
        } else {
            showNotification(data.error || 'Ошибка при отправке заявки', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Ошибка при отправке заявки', 'error');
    }
}

// Открыть модальное окно добавления книги
function openAddBookModal() {
    openModal('addBookModal');
}

// Обработчик формы добавления книги
document.addEventListener('DOMContentLoaded', () => {
    const addBookForm = document.getElementById('addBookForm');
    if (addBookForm) {
        addBookForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(addBookForm);
            appendCsrfToken(formData);

            try {
                const response = await fetch('api/add-book.php', {
                    method: 'POST',
                    headers: csrfHeaders(),
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message);
                    closeModal('addBookModal');
                    addBookForm.reset();
                    // Перезагрузить страницу для отображения новой книги
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.error || 'Ошибка при добавлении книги', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Ошибка при добавлении книги', 'error');
            }
        });
    }
});

// Вспомогательная функция для экранирования HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
