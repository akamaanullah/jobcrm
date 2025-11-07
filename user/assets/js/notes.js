// Notes Management JavaScript
class NotesManager {
    constructor() {
        this.notes = [];
        this.stats = {};
        this.currentSort = 'newest';
        this.currentSearch = '';
        this.init();
    }

    init() {
        this.initTinyMCE();
        this.loadNotes();
        this.bindEvents();
    }

    initTinyMCE() {
        // Initialize TinyMCE for Add Note modal
        tinymce.init({
            selector: '#noteContent',
            height: 300,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
                'alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | forecolor backcolor | removeformat | help',
            content_style: 'body { font-family: Poppins, Arial, sans-serif; font-size: 14px; }',
            branding: false,
            promotion: false,
            license_key: 'gpl'
        });

        // Initialize TinyMCE for Edit Note modal
        tinymce.init({
            selector: '#editNoteContent',
            height: 300,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
                'alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | forecolor backcolor | removeformat | help',
            content_style: 'body { font-family: Poppins, Arial, sans-serif; font-size: 14px; }',
            branding: false,
            promotion: false,
            license_key: 'gpl'
        });
    }

    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('notesSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.currentSearch = e.target.value;
                this.loadNotes();
            });
        }

        // Sort functionality
        const sortSelect = document.getElementById('notesSortFilter');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.currentSort = e.target.value;
                this.loadNotes();
            });
        }

        // Add note form
        const saveNoteBtn = document.getElementById('saveNoteBtn');
        if (saveNoteBtn) {
            saveNoteBtn.addEventListener('click', () => {
                this.handleAddNote();
            });
        }

        // Update note form
        const updateNoteBtn = document.getElementById('updateNoteBtn');
        if (updateNoteBtn) {
            updateNoteBtn.addEventListener('click', () => {
                this.handleUpdateNote();
            });
        }

        // Delete note confirmation
        const confirmDeleteBtn = document.getElementById('confirmDeleteNoteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                this.handleDeleteNote();
            });
        }

        // Color presets
        document.querySelectorAll('.color-preset').forEach(preset => {
            preset.addEventListener('click', (e) => {
                const color = e.target.dataset.color;
                const colorInput = e.target.closest('.color-picker-wrapper').querySelector('input[type="color"]');
                if (colorInput) {
                    colorInput.value = color;
                }
            });
        });

        // Modal cleanup
        document.getElementById('addNoteModal')?.addEventListener('hidden.bs.modal', () => {
            this.resetAddNoteForm();
        });

        document.getElementById('editNoteModal')?.addEventListener('hidden.bs.modal', () => {
            this.resetEditNoteForm();
        });
    }

    async loadNotes() {
        try {
            this.showLoading();

            const params = new URLSearchParams({
                sort: this.currentSort
            });

            if (this.currentSearch) {
                params.append('search', this.currentSearch);
            }

            const response = await fetch(`assets/api/get_notes.php?${params}`);
            const data = await response.json();

            if (data.success) {
                this.notes = data.data;
                this.stats = data.stats;
                this.renderNotes();
                this.updateStats();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Load Notes Error:', error);
            this.showError('Failed to load notes');
        }
    }

    renderNotes() {
        const notesGrid = document.getElementById('notesGrid');
        const emptyState = document.getElementById('emptyState');

        if (!notesGrid) return;

        if (this.notes.length === 0) {
            notesGrid.style.display = 'none';
            emptyState.style.display = 'block';
            return;
        }

        notesGrid.style.display = 'block';
        emptyState.style.display = 'none';

        notesGrid.innerHTML = this.notes.map(note => this.createNoteCard(note)).join('');
    }

    createNoteCard(note) {
        // Strip HTML tags for preview
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = note.content;
        const textContent = tempDiv.textContent || tempDiv.innerText || '';
        
        const truncatedContent = textContent.length > 150 
            ? textContent.substring(0, 150) + '...' 
            : textContent;

        return `
            <div class="note-card ${note.is_pinned ? 'pinned' : ''}" style="background-color: ${note.color};" data-note-id="${note.id}">
                <div class="note-header">
                    <div class="note-title-section">
                        ${note.is_pinned ? '<i class="bi bi-pin-fill pinned-icon" title="Pinned Note"></i>' : ''}
                        <h5 class="note-title">${this.escapeHtml(note.title)}</h5>
                    </div>
                    <div class="note-actions">
                        <button class="btn btn-sm ${note.is_pinned ? 'btn-outline-warning' : 'btn-outline-secondary'}" onclick="notesManager.togglePinned(${note.id})" title="${note.is_pinned ? 'Unpin Note' : 'Pin Note'}">
                            <i class="bi bi-pin${note.is_pinned ? '-fill' : ''}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="notesManager.editNote(${note.id})" title="Edit Note">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="notesManager.deleteNote(${note.id}, '${this.escapeHtml(note.title)}')" title="Delete Note">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>
                <div class="note-content">
                    <div class="note-preview">${truncatedContent}</div>
                </div>
                <div class="note-footer">
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> Created ${note.created_ago}
                        ${note.updated_at !== note.created_at ? `<br><i class="bi bi-arrow-clockwise"></i> Updated ${note.updated_ago}` : ''}
                    </small>
                </div>
            </div>
        `;
    }

    updateStats() {
        const totalNotesCount = document.getElementById('totalNotesCount');
        const recentNotesCount = document.getElementById('recentNotesCount');
        const favoriteNotesCount = document.getElementById('favoriteNotesCount');

        if (totalNotesCount) totalNotesCount.textContent = this.stats.total_notes || 0;
        if (recentNotesCount) recentNotesCount.textContent = this.stats.recent_notes || 0;
        if (favoriteNotesCount) favoriteNotesCount.textContent = this.stats.favorite_notes || 0;
    }

    async handleAddNote() {
        const form = document.getElementById('addNoteForm');
        const saveBtn = document.getElementById('saveNoteBtn');

        if (!form || !saveBtn) return;

        // Get content from TinyMCE editor
        const content = tinymce.get('noteContent').getContent();
        
        const formData = new FormData(form);
        const noteData = {
            title: formData.get('title'),
            content: content,
            color: formData.get('color') || '#ffffff'
        };

        // Validate form
        if (!this.validateNoteForm(noteData)) {
            return;
        }

        try {
            this.setButtonLoading(saveBtn, true);

            const response = await fetch('assets/api/add_note.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(noteData)
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess(result.message);
                this.resetAddNoteForm();
                bootstrap.Modal.getInstance(document.getElementById('addNoteModal'))?.hide();
                this.loadNotes();
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Add Note Error:', error);
            this.showError('Failed to add note');
        } finally {
            this.setButtonLoading(saveBtn, false);
        }
    }

    async handleUpdateNote() {
        const form = document.getElementById('editNoteForm');
        const updateBtn = document.getElementById('updateNoteBtn');

        if (!form || !updateBtn) return;

        // Get content from TinyMCE editor
        const content = tinymce.get('editNoteContent').getContent();
        
        const formData = new FormData(form);
        const noteData = {
            note_id: formData.get('note_id'),
            title: formData.get('title'),
            content: content,
            color: formData.get('color') || '#ffffff'
        };

        // Validate form
        if (!this.validateNoteForm(noteData)) {
            return;
        }

        try {
            this.setButtonLoading(updateBtn, true);

            const response = await fetch('assets/api/update_note.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(noteData)
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess(result.message);
                bootstrap.Modal.getInstance(document.getElementById('editNoteModal'))?.hide();
                this.loadNotes();
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Update Note Error:', error);
            this.showError('Failed to update note');
        } finally {
            this.setButtonLoading(updateBtn, false);
        }
    }

    async handleDeleteNote() {
        const noteId = this.currentDeleteNoteId;

        if (!noteId) return;

        try {
            const response = await fetch('assets/api/delete_note.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ note_id: noteId })
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess(result.message);
                bootstrap.Modal.getInstance(document.getElementById('deleteNoteModal'))?.hide();
                this.loadNotes();
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Delete Note Error:', error);
            this.showError('Failed to delete note');
        }
    }

    editNote(noteId) {
        const note = this.notes.find(n => n.id == noteId);
        if (!note) return;

        // Populate edit form
        document.getElementById('editNoteId').value = note.id;
        document.getElementById('editNoteTitle').value = note.title;
        
        // Set content in TinyMCE editor
        tinymce.get('editNoteContent').setContent(note.content);
        
        document.getElementById('editNoteColor').value = note.color;

        // Show edit modal
        const editModal = new bootstrap.Modal(document.getElementById('editNoteModal'));
        editModal.show();
    }

    deleteNote(noteId, noteTitle) {
        this.currentDeleteNoteId = noteId;

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteNoteModal'));
        deleteModal.show();
    }

    async togglePinned(noteId) {
        try {
            const response = await fetch('assets/api/toggle_pinned.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ note_id: noteId })
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess(result.message);
                this.loadNotes(); // Reload to update the order
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Toggle Pinned Error:', error);
            this.showError('Failed to update note');
        }
    }

    validateNoteForm(data) {
        if (!data.title || data.title.trim().length === 0) {
            this.showError('Please enter a note title');
            return false;
        }

        if (!data.content || data.content.trim().length === 0) {
            this.showError('Please enter note content');
            return false;
        }

        if (data.title.length > 255) {
            this.showError('Title must be less than 255 characters');
            return false;
        }

        return true;
    }

    resetAddNoteForm() {
        const form = document.getElementById('addNoteForm');
        if (form) {
            form.reset();
            document.getElementById('noteColor').value = '#ffffff';
            // Clear TinyMCE editor
            tinymce.get('noteContent').setContent('');
        }
    }

    resetEditNoteForm() {
        const form = document.getElementById('editNoteForm');
        if (form) {
            form.reset();
            document.getElementById('editNoteColor').value = '#ffffff';
            // Clear TinyMCE editor
            tinymce.get('editNoteContent').setContent('');
        }
    }

    setButtonLoading(button, loading) {
        if (!button) return;

        if (loading) {
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Processing...';
        } else {
            button.disabled = false;
            if (button.id === 'saveNoteBtn') {
                button.innerHTML = '<i class="bi bi-check-circle"></i> Save Note';
            } else if (button.id === 'updateNoteBtn') {
                button.innerHTML = '<i class="bi bi-check-circle"></i> Update Note';
            }
        }
    }

    showLoading() {
        const notesGrid = document.getElementById('notesGrid');
        const emptyState = document.getElementById('emptyState');
        const notesLoading = document.getElementById('notesLoading');

        if (notesLoading) {
            notesLoading.style.display = 'block';
        }
        if (notesGrid) {
            notesGrid.style.display = 'none';
        }
        if (emptyState) {
            emptyState.style.display = 'none';
        }
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="bi bi-x"></i>
            </button>
        `;

        // Add to notification container
        let container = document.getElementById('notificationContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notificationContainer';
            container.className = 'notification-container';
            document.body.appendChild(container);
        }

        container.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 3000);

        // Add animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize notes manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.notesManager = new NotesManager();
});
