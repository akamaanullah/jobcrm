    // Jobs Management JavaScript
    document.addEventListener('DOMContentLoaded', function () {

        // Initialize DOM elements
        const jobSLAInput = document.getElementById('jobSLA');
        const jobSLACalendarBtn = document.getElementById('jobSLACalendarBtn');
        const editJobSLAInput = document.getElementById('editJobSLA');
        const editJobSLACalendarBtn = document.getElementById('editJobSLACalendarBtn');
        const jobSearchInput = document.getElementById('jobSearchInput');
        const jobStatusFilter = document.getElementById('jobStatusFilter');
        const jobsTableContainer = document.getElementById('jobsTableContainer');
        const jobsTable = document.getElementById('jobsTable');

        // Set minimum date to current date (start of day)
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const minDateTime = today.toISOString().slice(0, 16);

        if (jobSLAInput) {
            jobSLAInput.min = minDateTime;
        }

        if (editJobSLAInput) {
            editJobSLAInput.min = minDateTime;
        }

        // Load jobs on page load
        loadJobs();

        // Calendar button click handlers
        if (jobSLACalendarBtn) {
            jobSLACalendarBtn.addEventListener('click', function () {
                if (jobSLAInput) {
                    jobSLAInput.focus();
                    if (typeof jobSLAInput.showPicker === 'function') {
                        jobSLAInput.showPicker();
                    } else {
                        // Fallback for browsers that don't support showPicker
                        jobSLAInput.click();
                    }
                }
            });
        }

        if (editJobSLACalendarBtn) {
            editJobSLACalendarBtn.addEventListener('click', function () {
                if (editJobSLAInput) {
                    editJobSLAInput.focus();
                    if (typeof editJobSLAInput.showPicker === 'function') {
                        editJobSLAInput.showPicker();
                    } else {
                        // Fallback for browsers that don't support showPicker
                        editJobSLAInput.click();
                    }
                }
            });
        }

        // Add Job Form Handler
        const addJobForm = document.getElementById('addJobForm');
        const createJobBtn = document.getElementById('createJobBtn');
        const addJobModal = document.getElementById('addjobsModal');

        if (createJobBtn) {
            createJobBtn.addEventListener('click', function () {
                handleAddJob();
            });
        }
        
        // Clear form when add job modal is opened
        if (addJobModal) {
            addJobModal.addEventListener('show.bs.modal', function () {
                // Clear form
                if (addJobForm) {
                    addJobForm.reset();
                }
                
                // Clear picture preview
                clearPicturePreview();
                
                // Additional clearing for any remaining data
                setTimeout(() => {
                    // Force clear all input fields
                    const inputs = addJobForm.querySelectorAll('input, textarea, select');
                    inputs.forEach(input => {
                        if (input.type === 'file') {
                            input.value = '';
                        } else if (input.type === 'datetime-local') {
                            // Don't reset datetime-local, let it keep current date
                        } else {
                            input.value = '';
                        }
                    });
                    
                    // Clear any remaining previews
                    clearPicturePreview();
                }, 100);
            });
            
            // Also clear on modal shown event (additional safety)
            addJobModal.addEventListener('shown.bs.modal', function () {
                setTimeout(() => {
                    clearPicturePreview();
                }, 50);
            });
            
            // Clear on modal hidden event (cleanup)
            addJobModal.addEventListener('hidden.bs.modal', function () {
                // Clear form completely when modal is closed
                if (addJobForm) {
                    addJobForm.reset();
                }
                clearPicturePreview();
            });
        }

        // Edit Job Form Handler
        const updateJobBtn = document.getElementById('updateJobBtn');
        const editJobModal = document.getElementById('editjobsModal');
        let currentEditJobId = null;

        if (updateJobBtn) {
            updateJobBtn.addEventListener('click', function () {
                handleUpdateJob();
            });
        }

        // Handle Edit Job Modal Show
        if (editJobModal) {
            editJobModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const jobId = button.getAttribute('data-job-id');
                if (jobId) {
                    currentEditJobId = jobId;
                    loadJobForEdit(jobId);
                }
            });
        }

        // Handle Delete Job Button Clicks
        document.addEventListener('click', function (event) {
            if (event.target.closest('.delete-btn') || event.target.closest('[data-action="delete-job"]')) {
                const button = event.target.closest('.delete-btn') || event.target.closest('[data-action="delete-job"]');
                const jobId = button.getAttribute('data-job-id');
                if (jobId) {
                    handleDeleteJob(jobId, button);
                }
            }

            // Handle Confirm Delete Job Button
            if (event.target.id === 'confirmDeleteJobBtn') {
                handleConfirmDeleteJob();
            }
        });

        // Handle Add Job
        async function handleAddJob() {
            const form = document.getElementById('addJobForm');
            const formData = new FormData(form);

            // Get form values
            const jobData = {
                storeName: document.getElementById('storeName').value.trim(),
                address: document.getElementById('address').value.trim(),
                jobType: document.getElementById('jobType').value.trim(),
                jobSLA: document.getElementById('jobSLA').value,
                jobDetails: document.getElementById('jobDetails').value.trim(),
                additionalNotes: document.getElementById('additionalNotes').value.trim()
            };

            // Validate form
            if (!validateAddJobForm(jobData)) {
                return;
            }

            // Show loading state
            createJobBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing Images...';
            createJobBtn.disabled = true;

            try {
                // Get job pictures as base64 with progress
                createJobBtn.innerHTML = '<i class="bi bi-image"></i> Compressing Images...';
                jobData.jobPictures = await getJobPicturesAsync();

                createJobBtn.innerHTML = '<i class="bi bi-cloud-upload"></i> Creating Job...';

                // Send API request
                const response = await fetch('assets/api/add_job.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(jobData)
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    showNotification('Job created successfully!', 'success');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(addJobModal);
                    modal.hide();
                    
                    // Refresh the entire page immediately
                    window.location.reload();

                } else {
                    console.error('Add Job API Error:', data.message); // Debug log
                    showNotification(data.message || 'Failed to create job', 'error');
                }

            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred while creating job', 'error');
            } finally {
                // Reset button state
                createJobBtn.innerHTML = 'Create Job';
                createJobBtn.disabled = false;
            }
        }

        // Validate Add Job Form
        function validateAddJobForm(jobData) {
            if (!jobData.storeName) {
                showNotification('Store name is required', 'error');
                return false;
            }

            if (!jobData.address) {
                showNotification('Address is required', 'error');
                return false;
            }

            if (!jobData.jobType) {
                showNotification('Job type is required', 'error');
                return false;
            }

            if (!jobData.jobSLA) {
                showNotification('Job SLA is required', 'error');
                return false;
            }

            if (!jobData.jobDetails) {
                showNotification('Job details are required', 'error');
                return false;
            }

            // Date restrictions removed for historical data entry
            // Allow any date/time including past dates

            return true;
        }

        // Load Jobs
        async function loadJobs() {
            // Show loading state
            if (jobsTableContainer) {
                jobsTableContainer.innerHTML = '<div class="text-center p-4"><i class="bi bi-hourglass-split"></i> Loading jobs...</div>';
            }

            // Build query parameters
            const searchTerm = jobSearchInput ? jobSearchInput.value : '';
            const statusFilter = jobStatusFilter ? jobStatusFilter.value : '';

            const params = new URLSearchParams();
            if (searchTerm) params.append('search', searchTerm);
            if (statusFilter) params.append('status', statusFilter);
            params.append('sort', 'created_at');

            try {
                // Fetch jobs and unread counts in parallel
                const [jobsResponse, unreadResponse] = await Promise.all([
                    fetch(`assets/api/get_jobs.php?${params.toString()}`),
                    fetch('assets/api/get_job_unread_counts.php')
                ]);

                const jobsData = await jobsResponse.json();
                const unreadData = await unreadResponse.json();

                if (jobsData.success) {
                    // Get unread counts
                    const unreadCounts = unreadData.success ? unreadData.data : {};

                    // Render jobs with unread counts
                    renderJobs(jobsData.jobs, unreadCounts);
                    updateJobMetrics(jobsData.stats);
                } else {
                    console.error('Get Jobs API Error:', jobsData.message); // Debug log
                    showNotification(jobsData.message || 'Failed to load jobs', 'error');
                    if (jobsTableContainer) {
                        jobsTableContainer.innerHTML = '<div class="text-center p-4 text-danger">Failed to load jobs</div>';
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred while loading jobs', 'error');
                if (jobsTableContainer) {
                    jobsTableContainer.innerHTML = '<div class="text-center p-4 text-danger">Error loading jobs</div>';
                }
            }
        }

        // Render Jobs
        function renderJobs(jobs, unreadCounts = {}) {
            if (!jobsTableContainer) return;

            if (jobs.length === 0) {
                jobsTableContainer.innerHTML = '<div class="text-center p-4">No jobs found</div>';
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table jobs-table" id="jobsTable">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAllJobs" class="form-check-input">
                                </th>
                                <th>STORE NAME</th>
                                <th>JOB TYPE</th>
                                <th>ADDRESS</th>
                                <th>SLA DEADLINE</th>
                                <th>STATUS</th>
                                <th>ASSIGNED TO</th>
                                <th>VENDORS</th>
                                <th>CREATED</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            jobs.forEach(job => {
                const statusClass = getStatusClass(job.status);
                const timeAgo = getTimeAgo(job.created_at);
                const slaDate = new Date(job.job_sla).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
                const slaTime = new Date(job.job_sla).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });

                html += `
                    <tr data-job-id="${job.id}">
                        <td>
                            <input type="checkbox" class="form-check-input job-checkbox" data-job-id="${job.id}">
                        </td>
                        <td>
                            <div class="store-info">
                                <div class="store-icon">
                                    <i class="bi bi-shop"></i>
                                </div>
                                <div class="store-details">
                                    <div class="store-name">${job.store_name}</div>
                                    <div class="job-id">JOB-${job.id}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="job-type">${job.job_type.toUpperCase()}</span>
                        </td>
                        <td>
                            <div class="address-info">
                                <i class="bi bi-geo-alt"></i>
                                <span>${job.address}</span>
                            </div>
                        </td>
                        <td>
                            <div class="deadline-info">
                                <i class="bi bi-calendar"></i>
                                <div class="deadline-details">
                                    <div>${slaDate}</div>
                                    <div class="time">${slaTime}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge ${statusClass}">${getStatusDisplayText(job.status)}</span>
                        </td>
                        <td>
                            <div class="assigned-user-info">
                                ${job.assigned_to_name ? `
                                    <div class="user-assigned">
                                        <i class="bi bi-person-check text-success"></i>
                                        <span class="assigned-user-name">${job.assigned_to_name}</span>
                                    </div>
                                ` : `
                                    <div class="user-not-assigned">
                                        <i class="bi bi-person-x text-muted"></i>
                                        <span class="text-muted">Not assigned</span>
                                    </div>
                                `}
                            </div>
                        </td>
                        <td>
                            <div class="vendor-info">
                                <span class="vendor-count">${job.vendor_count}</span>
                                ${unreadCounts[job.id] > 0 ? `
                                    <span class="unread-messages-badge" title="Unread messages">
                                        <i class="bi bi-chat-dots"></i>
                                        <span class="badge-count">${unreadCounts[job.id]}</span>
                                    </span>
                                ` : ''}
                            </div>
                        </td>
                        <td>
                            <div class="created-info">
                                <i class="bi bi-clock"></i>
                                <span>${timeAgo}</span>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="view-job.php?id=${job.id}">
                                    <button class="action-btn view-btn" title="View Job">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </a>
                                <button class="action-btn edit-btn" title="Edit Job" data-bs-toggle="modal" data-bs-target="#editjobsModal" data-job-id="${job.id}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="action-btn delete-btn" title="Delete Job" data-job-id="${job.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            jobsTableContainer.innerHTML = html;

            // Initialize bulk assignment functionality
            initializeBulkAssignment();
        }

        // Get Status Class
        function getStatusClass(status) {
            switch (status) {
                case 'added':
                    return 'bg-info';
                case 'in_progress':
                    return 'bg-warning';
                case 'completed':
                    return 'bg-success';
                default:
                    return 'bg-info';
            }
        }

        // Get Status Display Text
        function getStatusDisplayText(status) {
            switch (status) {
                case 'added':
                    return 'ADDED';
                case 'in_progress':
                    return 'IN PROGRESS';
                case 'completed':
                    return 'COMPLETED';
                default:
                    return 'ADDED';
            }
        }

        // Update Job Metrics
        function updateJobMetrics(stats) {
            if (!stats) {
                console.warn('No stats provided to updateJobMetrics');
                return;
            }

            // Update total jobs
            const totalJobsElement = document.getElementById('totalJobsCount');
            if (totalJobsElement && stats.total_jobs !== undefined) {
                totalJobsElement.textContent = stats.total_jobs;
            }

            // Update added jobs (jobs with status 'added')
            const pendingJobsElement = document.getElementById('pendingJobsCount');
            if (pendingJobsElement && stats.pending_jobs !== undefined) {
                pendingJobsElement.textContent = stats.pending_jobs;
            }

            // Update active jobs
            const activeJobsElement = document.getElementById('activeJobsCount');
            if (activeJobsElement && stats.active_jobs !== undefined) {
                activeJobsElement.textContent = stats.active_jobs;
            }

            // Update completed jobs
            const completedJobsElement = document.getElementById('completedJobsCount');
            if (completedJobsElement && stats.completed_jobs !== undefined) {
                completedJobsElement.textContent = stats.completed_jobs;
            }
        }

        // Show Notification
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

            // Add to body
            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }

        // Validate image files
        function validateImageFiles(files) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            const maxFiles = 10;

            if (files.length > maxFiles) {
                showNotification(`Maximum ${maxFiles} files allowed`, 'error');
                return false;
            }

            for (let file of files) {
                if (!allowedTypes.includes(file.type)) {
                    showNotification(`File ${file.name} is not a valid image format`, 'error');
                    return false;
                }

                if (file.size > maxSize) {
                    showNotification(`File ${file.name} is too large. Maximum size is 5MB`, 'error');
                    return false;
                }
            }

            return true;
        }

        // File input preview for job pictures
        const attachedPicturesInput = document.getElementById('attachedPictures');
        if (attachedPicturesInput) {
            attachedPicturesInput.addEventListener('change', function () {
                // Validate files before preview
                if (validateImageFiles(this.files)) {
                    previewJobPictures(this);
                } else {
                    this.value = ''; // Clear invalid files
                }
            });
        }

        const editAttachedPicturesInput = document.getElementById('editAttachedPictures');
        if (editAttachedPicturesInput) {
            editAttachedPicturesInput.addEventListener('change', function () {
                // Validate files before preview
                if (validateImageFiles(this.files)) {
                    previewJobPictures(this);
                } else {
                    this.value = ''; // Clear invalid files
                }
            });
        }

        // Preview Job Pictures
        function previewJobPictures(input) {
            const files = input.files;
            const previewContainer = input.parentNode.querySelector('.pictures-preview');
            const fileCountInfo = document.getElementById('fileCountInfo');

            // Remove existing preview
            if (previewContainer) {
                previewContainer.remove();
            }

            // Update file count info
            if (files.length > 0) {
                if (fileCountInfo) {
                    fileCountInfo.style.display = 'block';
                    fileCountInfo.querySelector('span').textContent = `${files.length} file${files.length > 1 ? 's' : ''} selected`;
                }

                // Create preview container
                const preview = document.createElement('div');
                preview.className = 'pictures-preview mt-2';
                preview.innerHTML = '<h6>Selected Pictures:</h6>';

                Array.from(files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const imagePreview = document.createElement('div');
                            imagePreview.className = 'image-preview-item d-inline-block me-2 mb-2';
                            imagePreview.innerHTML = `
                                <div class="position-relative">
                                    <img src="${e.target.result}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                    <button type="button" class="btn btn-danger position-absolute" style="width: 20px; height: 20px; top: 5px; right: 5px; " onclick="removePicturePreview(this)" title="Remove image">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <div class="text-center mt-1">
                                    <small class="text-muted" style="font-size: 0.7rem; max-width: 80px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block;">${file.name}</small>
                                </div>
                            `;
                            preview.appendChild(imagePreview);
                        };
                        reader.readAsDataURL(file);
                    }
                });

                input.parentNode.appendChild(preview);
            } else {
                // Hide file count info when no files
                if (fileCountInfo) {
                    fileCountInfo.style.display = 'none';
                }
            }
        }

        // Get Job Pictures as Base64
        function getJobPictures() {
            const attachedPicturesInput = document.getElementById('attachedPictures');
            if (!attachedPicturesInput || !attachedPicturesInput.files.length) {
                return [];
            }

            const pictures = [];
            const files = Array.from(attachedPicturesInput.files);

            // Convert files to base64 (simplified version - in real app, you'd use Promise.all)
            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        pictures.push(e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            return pictures;
        }

        // Get Job Pictures as Base64 (Optimized Async version)
        function getJobPicturesAsync() {
            return new Promise((resolve) => {
                const attachedPicturesInput = document.getElementById('attachedPictures');
                if (!attachedPicturesInput || !attachedPicturesInput.files.length) {
                    resolve([]);
                    return;
                }

                const pictures = [];
                const files = Array.from(attachedPicturesInput.files);
                let processedFiles = 0;

                if (files.length === 0) {
                    resolve([]);
                    return;
                }

                // Validate file sizes first
                const maxSize = 5 * 1024 * 1024; // 5MB per file
                const validFiles = files.filter(file => {
                    if (file.size > maxSize) {
                        showNotification(`File ${file.name} is too large. Maximum size is 5MB.`, 'error');
                        return false;
                    }
                    return file.type.startsWith('image/');
                });

                if (validFiles.length === 0) {
                    resolve([]);
                    return;
                }

                // Process files with compression
                validFiles.forEach(file => {
                    compressAndConvertToBase64(file).then(compressedData => {
                        pictures.push(compressedData);
                        processedFiles++;

                        if (processedFiles === validFiles.length) {
                            resolve(pictures);
                        }
                    }).catch(error => {
                        console.error('Error processing file:', error);
                        processedFiles++;
                        if (processedFiles === validFiles.length) {
                            resolve(pictures);
                        }
                    });
                });
            });
        }

        // Compress and convert image to base64
        function compressAndConvertToBase64(file) {
            return new Promise((resolve, reject) => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const img = new Image();

                img.onload = function () {
                    // Calculate new dimensions (max 1920x1080)
                    const maxWidth = 1920;
                    const maxHeight = 1080;
                    let { width, height } = img;

                    if (width > maxWidth || height > maxHeight) {
                        const ratio = Math.min(maxWidth / width, maxHeight / height);
                        width *= ratio;
                        height *= ratio;
                    }

                    // Set canvas dimensions
                    canvas.width = width;
                    canvas.height = height;

                    // Draw and compress
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convert to base64 with compression
                    const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.8);
                    resolve(compressedDataUrl);
                };

                img.onerror = function () {
                    reject(new Error('Failed to load image'));
                };

                // Load image
                const reader = new FileReader();
                reader.onload = function (e) {
                    img.src = e.target.result;
                };
                reader.onerror = function () {
                    reject(new Error('Failed to read file'));
                };
                reader.readAsDataURL(file);
            });
        }

        // Remove picture preview
        window.removePicturePreview = function (button) {
            const previewItem = button.closest('.image-preview-item');
            if (previewItem) {
                previewItem.remove();
                
                // Update file count after removal
                const previewContainer = previewItem.closest('.pictures-preview');
                if (previewContainer) {
                    const remainingImages = previewContainer.querySelectorAll('.image-preview-item');
                    const fileCountInfo = document.getElementById('fileCountInfo');
                    
                    if (remainingImages.length === 0) {
                        // No more images, hide file count and remove preview container
                        if (fileCountInfo) {
                            fileCountInfo.style.display = 'none';
                        }
                        previewContainer.remove();
                    } else if (fileCountInfo) {
                        // Update file count
                        fileCountInfo.querySelector('span').textContent = `${remainingImages.length} file${remainingImages.length > 1 ? 's' : ''} selected`;
                    }
                }
            }
        };
        
        // Clear all picture previews
        function clearPicturePreview() {
            // Clear add job form picture preview
            const addJobPreview = document.querySelector('#addjobsModal .pictures-preview');
            if (addJobPreview) {
                addJobPreview.remove();
            }
            
            // Clear edit job form picture preview
            const editJobPreview = document.querySelector('#editjobsModal .pictures-preview');
            if (editJobPreview) {
                editJobPreview.remove();
            }
            
            // Clear existing pictures preview in edit modal
            const existingPicturesPreview = document.getElementById('existingPicturesPreview');
            if (existingPicturesPreview) {
                existingPicturesPreview.innerHTML = '';
            }
            
            // Clear file count info
            const fileCountInfo = document.getElementById('fileCountInfo');
            if (fileCountInfo) {
                fileCountInfo.style.display = 'none';
            }
            
            // Clear file inputs in add job modal
            const addJobFileInput = document.getElementById('attachedPictures');
            if (addJobFileInput) {
                addJobFileInput.value = '';
            }
            
            // Clear file inputs in edit job modal
            const editJobFileInput = document.getElementById('editAttachedPictures');
            if (editJobFileInput) {
                editJobFileInput.value = '';
            }
            
            // Remove any remaining preview elements
            const allPreviews = document.querySelectorAll('.image-preview-item');
            allPreviews.forEach(preview => {
                if (preview.closest('#addjobsModal')) {
                    preview.remove();
                }
            });
        }

        // Search and Filter Event Listeners
        if (jobSearchInput) {
            jobSearchInput.addEventListener('input', function () {
                filterJobs();
            });
        }

        if (jobStatusFilter) {
            jobStatusFilter.addEventListener('change', function () {
                filterJobs();
            });
        }

        // Filter Jobs Function
        function filterJobs() {
            // Reload jobs with current filters
            loadJobs();
        }

        // Load Job for Edit
        async function loadJobForEdit(jobId) {
            try {
                // Show loading state
                const modalBody = document.querySelector('#editjobsModal .modal-body');
                modalBody.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading job details...</p>
                    </div>
                `;

                const response = await fetch(`assets/api/get_job_details.php?job_id=${jobId}`);
                const data = await response.json();

                if (data.success) {
                    populateEditForm(data.job);
                } else {
                    showNotification(data.message || 'Failed to load job details', 'error');
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(editJobModal);
                    modal.hide();
                }
            } catch (error) {
                console.error('Load Job for Edit Error:', error);
                showNotification('An error occurred while loading job details', 'error');
                // Close modal
                const modal = bootstrap.Modal.getInstance(editJobModal);
                modal.hide();
            }
        }

        // Populate Edit Form
        function populateEditForm(jobData) {
            // Restore the form HTML
            const modalBody = document.querySelector('#editjobsModal .modal-body');
            modalBody.innerHTML = `
                <form id="editJobForm">
                    <div class="mb-3">
                        <label for="editStoreName" class="form-label">Store Name</label>
                        <input type="text" class="form-control" id="editStoreName" name="storeName" placeholder="Store Name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Address</label>
                        <input type="text" class="form-control" id="editAddress" name="address" placeholder="Address" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editJobType" class="form-label">Job Type</label>
                        <input type="text" class="form-control" id="editJobType" name="jobType" placeholder="e.g. Delivery, Pickup, Service, Maintenance" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editJobSLA" class="form-label">Job SLA</label>
                        <div class="input-group">
                            <input type="datetime-local" class="form-control" id="editJobSLA" name="jobSLA" required>
                            <button class="btn btn-outline-secondary" type="button" id="editJobSLACalendarBtn">
                                <i class="bi bi-calendar"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editJobDetails" class="form-label">Job Details</label>
                        <textarea class="form-control" id="editJobDetails" name="jobDetails" rows="4" placeholder="Describe the job requirements, specifications, and any important details..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editAttachedPictures" class="form-label">Attached Pictures</label>
                        <input type="file" class="form-control" id="editAttachedPictures" name="attachedPictures" multiple accept="image/*">
                        <div class="file-info">
                            <i class="bi bi-info-circle"></i>
                            <span>You can select multiple images (JPG, PNG, GIF)</span>
                        </div>
                        <div id="existingPicturesPreview" class="existing-pictures-preview mt-3"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editAdditionalNotes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="editAdditionalNotes" name="additionalNotes" rows="3" placeholder="Any additional information, special instructions, or notes..."></textarea>
                    </div>
                </form>
            `;

            // Populate form fields
            document.getElementById('editStoreName').value = jobData.store_name || '';
            document.getElementById('editAddress').value = jobData.address || '';
            document.getElementById('editJobType').value = jobData.job_type || '';
            document.getElementById('editJobDetails').value = jobData.job_detail || '';
            document.getElementById('editAdditionalNotes').value = jobData.additional_notes || '';

            // Format SLA date for datetime-local input
            if (jobData.job_sla) {
                const slaDate = new Date(jobData.job_sla);
                // Check if date is valid
                if (!isNaN(slaDate.getTime())) {
                    const formattedDate = slaDate.toISOString().slice(0, 16);
                    document.getElementById('editJobSLA').value = formattedDate;
                }
            }

            // For editing existing jobs, don't set minimum date restriction
            // as old jobs might have past SLA dates

            // Re-attach event listeners
            const editJobSLACalendarBtn = document.getElementById('editJobSLACalendarBtn');
            if (editJobSLACalendarBtn) {
                editJobSLACalendarBtn.addEventListener('click', function () {
                    const editJobSLAInput = document.getElementById('editJobSLA');
                    if (editJobSLAInput) {
                        editJobSLAInput.focus();
                        if (typeof editJobSLAInput.showPicker === 'function') {
                            editJobSLAInput.showPicker();
                        } else {
                            editJobSLAInput.click();
                        }
                    }
                });
            }

            const editAttachedPicturesInput = document.getElementById('editAttachedPictures');
            if (editAttachedPicturesInput) {
                editAttachedPicturesInput.addEventListener('change', function () {
                    previewJobPictures(this);
                });
            }

            // Display existing pictures
            displayExistingPictures(jobData.pictures);
        }

        // Handle Update Job
        async function handleUpdateJob() {
            if (!currentEditJobId) {
                showNotification('No job selected for editing', 'error');
                return;
            }

            // Get form values
            const jobData = {
                job_id: currentEditJobId,
                store_name: document.getElementById('editStoreName').value.trim(),
                address: document.getElementById('editAddress').value.trim(),
                job_type: document.getElementById('editJobType').value.trim(),
                job_sla: document.getElementById('editJobSLA').value,
                job_details: document.getElementById('editJobDetails').value.trim(),
                additional_notes: document.getElementById('editAdditionalNotes').value.trim()
            };

            // Validate form
            if (!validateUpdateJobForm(jobData)) {
                return;
            }

            // Show loading state
            updateJobBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';
            updateJobBtn.disabled = true;

            try {
                // Get new pictures as base64
                jobData.new_pictures = await getEditJobPicturesAsync();

                // Send API request
                const response = await fetch('assets/api/update_job.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(jobData)
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    showNotification('Job updated successfully!', 'success');

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(editJobModal);
                    modal.hide();

                    // Refresh jobs list
                    loadJobs();

                } else {
                    showNotification(data.message || 'Failed to update job', 'error');
                }

            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred while updating job', 'error');
            } finally {
                // Reset button state
                updateJobBtn.innerHTML = 'Update Job';
                updateJobBtn.disabled = false;
            }
        }

        // Validate Update Job Form
        function validateUpdateJobForm(jobData) {
            if (!jobData.store_name) {
                showNotification('Store name is required', 'error');
                return false;
            }

            if (!jobData.address) {
                showNotification('Address is required', 'error');
                return false;
            }

            if (!jobData.job_type) {
                showNotification('Job type is required', 'error');
                return false;
            }

            if (!jobData.job_sla) {
                showNotification('Job SLA is required', 'error');
                return false;
            }

            if (!jobData.job_details) {
                showNotification('Job details are required', 'error');
                return false;
            }

            // Date restrictions removed for historical data entry
            // Allow any date/time including past dates

            return true;
        }

        // Display Existing Pictures
        function displayExistingPictures(pictures) {
            const existingPicturesPreview = document.getElementById('existingPicturesPreview');
            if (!existingPicturesPreview) return;

            if (!pictures || pictures.length === 0) {
                existingPicturesPreview.innerHTML = '';
                return;
            }

            let html = '<h6 class="mb-3"><i class="bi bi-images"></i> Existing Pictures:</h6>';
            html += '<div class="row g-2">';

            pictures.forEach((picture, index) => {
                // Fix the picture path - ensure correct relative path from admin directory
                let picturePath = picture.picture_path;

                // Remove ../../../ if present
                if (picturePath.startsWith('../../../')) {
                    picturePath = picturePath.replace('../../../', '');
                }

                // If path doesn't start with ../, add it for admin directory context
                if (!picturePath.startsWith('../') && !picturePath.startsWith('http')) {
                    picturePath = '../' + picturePath;
                }


                html += `
                    <div class="col-md-3 col-sm-4 col-6">
                        <div class="existing-picture-item position-relative">
                            <img src="${picturePath}" 
                                alt="${picture.picture_name}" 
                                class="img-thumbnail existing-picture-img"
                                style="width: 100%; height: 100px; object-fit: cover; cursor: pointer;"
                                onclick="viewExistingPicture('${picturePath}', '${picture.picture_name}')">
                            <div class="existing-picture-overlay">
                                <button type="button" 
                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                                        onclick="removeExistingPicture(${picture.id}, this)"
                                        title="Remove Picture">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <div class="existing-picture-name text-center mt-1">
                                <small class="text-muted text-truncate d-block" title="${picture.picture_name}">
                                    ${picture.picture_name}
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            existingPicturesPreview.innerHTML = html;
        }

        // View Existing Picture
        window.viewExistingPicture = function (picturePath, pictureName) {
            // Create modal for viewing picture
            const modalHtml = `
                <div class="modal fade" id="viewPictureModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${pictureName}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="${picturePath}" alt="${pictureName}" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if any
            const existingModal = document.getElementById('viewPictureModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add new modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewPictureModal'));
            modal.show();

            // Remove modal from DOM when hidden
            document.getElementById('viewPictureModal').addEventListener('hidden.bs.modal', function () {
                this.remove();
            });
        };

        // Remove Existing Picture
        window.removeExistingPicture = function (pictureId, button) {
            if (confirm('Are you sure you want to remove this picture? This action cannot be undone.')) {
                // Show loading state on button
                const originalContent = button.innerHTML;
                button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                button.disabled = true;

                // Call API to delete picture
                fetch('assets/api/delete_job_picture.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        picture_id: pictureId
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove from UI
                            const pictureItem = button.closest('.col-md-3');
                            pictureItem.remove();

                            showNotification('Picture removed successfully', 'success');

                            // Check if no pictures left
                            const existingPicturesPreview = document.getElementById('existingPicturesPreview');
                            const remainingPictures = existingPicturesPreview.querySelectorAll('.col-md-3');
                            if (remainingPictures.length === 0) {
                                existingPicturesPreview.innerHTML = '';
                            }
                        } else {
                            showNotification(data.message || 'Failed to remove picture', 'error');
                            // Reset button
                            button.innerHTML = originalContent;
                            button.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error removing picture:', error);
                        showNotification('An error occurred while removing picture', 'error');
                        // Reset button
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    });
            }
        };

        // Get Edit Job Pictures as Base64 (Async version)
        function getEditJobPicturesAsync() {
            return new Promise((resolve) => {
                const editAttachedPicturesInput = document.getElementById('editAttachedPictures');
                if (!editAttachedPicturesInput || !editAttachedPicturesInput.files.length) {
                    resolve([]);
                    return;
                }

                const pictures = [];
                const files = Array.from(editAttachedPicturesInput.files);
                let processedFiles = 0;

                if (files.length === 0) {
                    resolve([]);
                    return;
                }

                files.forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            pictures.push({
                                name: file.name,
                                data: e.target.result
                            });
                            processedFiles++;

                            if (processedFiles === files.length) {
                                resolve(pictures);
                            }
                        };
                        reader.readAsDataURL(file);
                    } else {
                        processedFiles++;
                        if (processedFiles === files.length) {
                            resolve(pictures);
                        }
                    }
                });
            });
        }

        // Handle Delete Job
        function handleDeleteJob(jobId, button) {
            // Get job name for confirmation
            const jobRow = button.closest('tr');
            const jobName = jobRow ? jobRow.querySelector('.store-name')?.textContent || 'this job' : 'this job';

            // Store current job data for deletion
            window.currentDeleteJob = {
                id: jobId,
                name: jobName,
                button: button,
                row: jobRow
            };

            // Show job name in modal
            document.getElementById('deleteJobName').textContent = `"${jobName}"`;

            // Show confirmation modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteJobModal'));
            deleteModal.show();
        }

        // Handle Confirm Delete Job
        async function handleConfirmDeleteJob() {
            const jobData = window.currentDeleteJob;
            if (!jobData) return;

            const button = jobData.button;

            // Show loading state
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            button.disabled = true;

            // Disable modal buttons
            const confirmBtn = document.getElementById('confirmDeleteJobBtn');
            const cancelBtn = document.querySelector('#deleteJobModal .btn-secondary');
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Deleting...';

            try {
                // Call delete API
                const response = await fetch('assets/api/delete_job.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        job_id: jobData.id
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Hide modal
                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteJobModal'));
                    deleteModal.hide();

                    // Show success message
                    showNotification(data.message || 'Job deleted successfully', 'success');

                    // Remove job row from table
                    if (jobData.row) {
                        jobData.row.remove();
                    }

                    // Refresh jobs list to update stats
                    loadJobs();

                } else {
                    showNotification(data.message || 'Failed to delete job', 'error');
                    // Reset button
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }

            } catch (error) {
                console.error('Error deleting job:', error);
                showNotification('An error occurred while deleting job', 'error');
                // Reset button
                button.innerHTML = originalContent;
                button.disabled = false;
            } finally {
                // Reset modal buttons
                confirmBtn.disabled = false;
                cancelBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="bi bi-trash3-fill me-1"></i>Delete Job';

                // Clear current delete job data
                window.currentDeleteJob = null;
            }
        }

        // Get Time Ago Helper Function
        function getTimeAgo(datetime) {
            const time = Date.now() - new Date(datetime).getTime();

            if (time < 60000) {
                return 'just now';
            } else if (time < 3600000) {
                const minutes = Math.floor(time / 60000);
                return minutes + ' minute' + (minutes > 1 ? 's' : '') + ' ago';
            } else if (time < 86400000) {
                const hours = Math.floor(time / 3600000);
                return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
            } else if (time < 2592000000) {
                const days = Math.floor(time / 86400000);
                return days + ' day' + (days > 1 ? 's' : '') + ' ago';
            } else if (time < 31536000000) {
                const months = Math.floor(time / 2592000000);
                return months + ' month' + (months > 1 ? 's' : '') + ' ago';
            } else {
                const years = Math.floor(time / 31536000000);
                return years + ' year' + (years > 1 ? 's' : '') + ' ago';
            }
        }

        // Bulk Assignment Functions
        async function initializeBulkAssignment() {
            // Load users for assignment dropdown
            await loadUsersForAssignment();

            // Initialize event listeners
            const selectAllCheckbox = document.getElementById('selectAllJobs');
            const jobCheckboxes = document.querySelectorAll('.job-checkbox');
            const bulkAssignmentControls = document.getElementById('bulkAssignmentControls');
            const selectedJobsCount = document.getElementById('selectedJobsCount');
            const assignUserSelect = document.getElementById('assignUserSelect');
            const assignSelectedBtn = document.getElementById('assignSelectedBtn');
            const clearSelectionBtn = document.getElementById('clearSelectionBtn');

            // Select All checkbox handler
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function () {
                    const isChecked = this.checked;
                    jobCheckboxes.forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                    updateBulkAssignmentUI();
                });
            }

            // Individual job checkbox handlers
            jobCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    updateSelectAllState();
                    updateBulkAssignmentUI();
                });
            });

            // Assign button handler
            if (assignSelectedBtn) {
                assignSelectedBtn.addEventListener('click', assignSelectedJobs);
            }

            // Clear selection handler
            if (clearSelectionBtn) {
                clearSelectionBtn.addEventListener('click', clearAllSelections);
            }

            function updateSelectAllState() {
                if (selectAllCheckbox) {
                    const checkedBoxes = document.querySelectorAll('.job-checkbox:checked');
                    const totalBoxes = document.querySelectorAll('.job-checkbox');

                    selectAllCheckbox.checked = checkedBoxes.length === totalBoxes.length && totalBoxes.length > 0;
                    selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < totalBoxes.length;
                }
            }

            function updateBulkAssignmentUI() {
                const checkedBoxes = document.querySelectorAll('.job-checkbox:checked');
                const count = checkedBoxes.length;

                if (count > 0) {
                    bulkAssignmentControls.style.display = 'flex';
                    selectedJobsCount.textContent = count;
                    assignSelectedBtn.disabled = !assignUserSelect.value;
                } else {
                    bulkAssignmentControls.style.display = 'none';
                }
            }

            function clearSelection() {
                jobCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
                updateBulkAssignmentUI();
            }

            // Enable/disable assign button based on user selection
            if (assignUserSelect) {
                assignUserSelect.addEventListener('change', function () {
                    const checkedBoxes = document.querySelectorAll('.job-checkbox:checked');
                    assignSelectedBtn.disabled = checkedBoxes.length === 0 || !this.value;
                });
            }
        }

        async function loadUsersForAssignment() {
            try {
                const response = await fetch('assets/api/get_users_for_assignment.php');
                const data = await response.json();

                if (data.success) {
                    const assignUserSelect = document.getElementById('assignUserSelect');
                    if (assignUserSelect) {
                        assignUserSelect.innerHTML = '<option value="">Select User to Assign</option>';
                        data.users.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = `${user.first_name} ${user.last_name}`;
                            assignUserSelect.appendChild(option);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        async function assignSelectedJobs() {
            const checkedBoxes = document.querySelectorAll('.job-checkbox:checked');
            const assignUserSelect = document.getElementById('assignUserSelect');

            if (checkedBoxes.length === 0 || !assignUserSelect.value) {
                showNotification('Please select jobs and a user to assign', 'error');
                return;
            }

            const jobIds = Array.from(checkedBoxes).map(checkbox => checkbox.dataset.jobId);
            const userId = assignUserSelect.value;
            const userName = assignUserSelect.options[assignUserSelect.selectedIndex].text;

            try {
                const response = await fetch('assets/api/bulk_assign_jobs.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        job_ids: jobIds,
                        user_id: userId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(`Successfully assigned ${jobIds.length} jobs to ${userName}`, 'success');
                    loadJobs(); // Reload jobs to show updated assignments
                    clearAllSelections(); // Use the correct function name
                    assignUserSelect.value = '';
                } else {
                    throw new Error(data.message || 'Failed to assign jobs');
                }
            } catch (error) {
                console.error('Error assigning jobs:', error);
                showNotification(error.message || 'Failed to assign jobs', 'error');
            }
        }

        // Clear all selections function (accessible globally)
        function clearAllSelections() {
            const jobCheckboxes = document.querySelectorAll('.job-checkbox');
            const selectAllCheckbox = document.getElementById('selectAllJobs');

            jobCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }

            // Hide bulk assignment controls
            const bulkAssignmentControls = document.getElementById('bulkAssignmentControls');
            if (bulkAssignmentControls) {
                bulkAssignmentControls.style.display = 'none';
            }
        }

    });
