<?php $pageTitle = 'My Notes'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Dashboard Content -->
    <main class="dashboard-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2>My Notes</h2>
                <p>Create, organize, and manage your personal notes</p>
            </div>
            <div class="welcome-actions">
                <button class="btn btn-back" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                    <i class="bi bi-plus-circle"></i> Add New Note
                </button>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="metrics-row" id="metricsRow">
            <div class="metric-card" id="metricCardTotalNotes">
                <div class="metric-icon notes">
                    <i class="bi bi-sticky"></i>
                </div>
                <div class="metric-content">
                    <h3 id="totalNotesCount">0</h3>
                    <p class="metric-label">TOTAL NOTES</p>
                    <span class="metric-status text-success">All your notes</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardRecentNotes">
                <div class="metric-icon recent">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="metric-content">
                    <h3 id="recentNotesCount">0</h3>
                    <p class="metric-label">RECENT NOTES</p>
                    <span class="metric-status text-info">Last 7 days</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardFavoriteNotes">
                <div class="metric-icon favorite">
                    <i class="bi bi-heart"></i>
                </div>
                <div class="metric-content">
                    <h3 id="favoriteNotesCount">0</h3>
                    <p class="metric-label">IMPORTANT</p>
                    <span class="metric-status text-warning">Starred notes</span>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <div class="row g-3">
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="search-box">
                        <label>Search Notes</label>
                        <div class="search-input-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" class="search-input" id="notesSearchInput" 
                                   placeholder="Search notes by title or content...">
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-5 col-12">
                    <div class="filter-dropdowns">
                        <div class="dropdown-wrapper">
                            <label>Sort By</label>
                            <select class="filter-dropdown" id="notesSortFilter">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="title">Title A-Z</option>
                                <option value="updated">Recently Updated</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Grid -->
        <div class="notes-grid" id="notesGrid">
            <!-- Notes will be loaded dynamically -->
            <div class="text-center py-5" id="notesLoading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading your notes...</p>
            </div>
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-state-content">
                <div class="empty-state-icon">
                    <i class="bi bi-sticky"></i>
                </div>
                <h3>No Notes Yet</h3>
                <p>Start creating your first note to organize your thoughts and ideas.</p>
                <button class="btn btn-back" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                    <i class="bi bi-plus-circle"></i> Create Your First Note
                </button>
            </div>
        </div>
    </main>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteModalLabel">
                    <i class="bi bi-plus-circle"></i> Add New Note
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addNoteForm">
                    <div class="mb-3">
                        <label for="noteTitle" class="form-label">
                            <i class="bi bi-card-text"></i> Note Title
                        </label>
                        <input type="text" class="form-control" id="noteTitle" name="title" 
                               placeholder="Enter note title..." required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="noteContent" class="form-label">
                            <i class="bi bi-file-text"></i> Note Content
                        </label>
                        <textarea class="form-control" id="noteContent" name="content" rows="8" 
                                  placeholder="Write your note content here..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="noteColor" class="form-label">
                            <i class="bi bi-palette"></i> Note Color
                        </label>
                        <div class="color-picker-wrapper">
                            <input type="color" class="form-control form-control-color" id="noteColor" 
                                   name="color" value="#ffffff" title="Choose note color">
                            <div class="color-presets">
                                <div class="color-preset" data-color="#ffffff" style="background-color: #ffffff;" title="White"></div>
                                <div class="color-preset" data-color="#fff3cd" style="background-color: #fff3cd;" title="Yellow"></div>
                                <div class="color-preset" data-color="#d1ecf1" style="background-color: #d1ecf1;" title="Blue"></div>
                                <div class="color-preset" data-color="#d4edda" style="background-color: #d4edda;" title="Green"></div>
                                <div class="color-preset" data-color="#f8d7da" style="background-color: #f8d7da;" title="Red"></div>
                                <div class="color-preset" data-color="#e2e3e5" style="background-color: #e2e3e5;" title="Gray"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="saveNoteBtn">
                    <i class="bi bi-check-circle"></i> Save Note
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Note Modal -->
<div class="modal fade" id="editNoteModal" tabindex="-1" aria-labelledby="editNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editNoteModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Note
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editNoteForm">
                    <input type="hidden" id="editNoteId" name="note_id">
                    
                    <div class="mb-3">
                        <label for="editNoteTitle" class="form-label">
                            <i class="bi bi-card-text"></i> Note Title
                        </label>
                        <input type="text" class="form-control" id="editNoteTitle" name="title" 
                               placeholder="Enter note title..." required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editNoteContent" class="form-label">
                            <i class="bi bi-file-text"></i> Note Content
                        </label>
                        <textarea class="form-control" id="editNoteContent" name="content" rows="8" 
                                  placeholder="Write your note content here..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editNoteColor" class="form-label">
                            <i class="bi bi-palette"></i> Note Color
                        </label>
                        <div class="color-picker-wrapper">
                            <input type="color" class="form-control form-control-color" id="editNoteColor" 
                                   name="color" value="#ffffff" title="Choose note color">
                            <div class="color-presets">
                                <div class="color-preset" data-color="#ffffff" style="background-color: #ffffff;" title="White"></div>
                                <div class="color-preset" data-color="#fff3cd" style="background-color: #fff3cd;" title="Yellow"></div>
                                <div class="color-preset" data-color="#d1ecf1" style="background-color: #d1ecf1;" title="Blue"></div>
                                <div class="color-preset" data-color="#d4edda" style="background-color: #d4edda;" title="Green"></div>
                                <div class="color-preset" data-color="#f8d7da" style="background-color: #f8d7da;" title="Red"></div>
                                <div class="color-preset" data-color="#e2e3e5" style="background-color: #e2e3e5;" title="Gray"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="updateNoteBtn">
                    <i class="bi bi-check-circle"></i> Update Note
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Note Confirmation Modal -->
<div class="modal fade" id="deleteNoteModal" tabindex="-1" aria-labelledby="deleteNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteNoteModalLabel">
                    <i class="bi bi-exclamation-triangle"></i> Delete Note
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="bi bi-trash3 text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h5>Are you sure you want to delete this note?</h5>
                    <p class="text-muted mb-3">
                        This action cannot be undone. The note will be permanently removed.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteNoteBtn">
                    <i class="bi bi-trash3"></i> Delete Note
                </button>
            </div>
        </div>
    </div>
</div>

<!-- TinyMCE Rich Text Editor -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.7.0/tinymce.min.js"></script>

<script src="assets/js/notes.js"></script>
<?php include 'footer.php'; ?>