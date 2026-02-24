<!-- Модальное окно: Выдача книги -->
<div id="issueModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('issueModal')">&times;</span>
        <h2>📋 Выдать книгу: <span id="issueReaderName"></span></h2>
        <p class="subtitle">Выберите книгу из доступных для выдачи</p>

        <div id="availableBooksGrid" class="books-grid-small">
            <!-- Заполняется через JavaScript -->
        </div>

        <input type="hidden" id="issueReaderId">
    </div>
</div>

<!-- Модальное окно: Продление срока -->
<div id="extendModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('extendModal')">&times;</span>
        <h2>⏰ Продлить срок</h2>
        <p class="subtitle">Продление срока возврата для <span id="extendReaderName"></span></p>

        <div class="book-modal-info">
            <span class="book-icon-large">📚</span>
            <div>
                <strong id="extendBookTitle"></strong><br>
                <small id="extendBookAuthor"></small>
            </div>
        </div>

        <form id="extendForm">
            <input type="hidden" id="extendLoanId">
            <div class="form-group">
                <label for="extendDays">Продлить на (дней)</label>
                <select id="extendDays" name="days" class="form-control" required>
                    <option value="7">7 дней</option>
                    <option value="14" selected>14 дней</option>
                    <option value="21">21 день</option>
                    <option value="30">30 дней</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('extendModal')">
                    Отмена
                </button>
                <button type="submit" class="btn btn-primary">
                    ⏰ Продлить
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно: Принятие книги -->
<div id="returnModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('returnModal')">&times;</span>
        <h2>✅ Принять книгу</h2>
        <p class="subtitle">Подтвердите возврат книги от <span id="returnReaderName"></span></p>

        <div class="book-modal-info">
            <span class="book-icon-large">📚</span>
            <div>
                <strong id="returnBookTitle"></strong><br>
                <small id="returnBookAuthor"></small>
            </div>
        </div>

        <form id="returnForm">
            <input type="hidden" id="returnLoanId">
            <input type="hidden" id="returnBookId">

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('returnModal')">
                    Отмена
                </button>
                <button type="submit" class="btn btn-success">
                    ✓ Принять
                </button>
            </div>
        </form>
    </div>
</div>
