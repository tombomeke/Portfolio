// /public/js/modal.js
/**
 * ============================================
 * UNIVERSAL MODAL SYSTEM
 * ============================================
 * Beautiful, accessible modals for skills, projects, education, and learning goals
 * Features:
 * - Keyboard navigation (ESC to close, Tab trap)
 * - Click outside to close
 * - Smooth animations
 * - Responsive design
 * - Multi-language support
 */

class ModalSystem {
    constructor() {
        this.currentModal = null;
        this.modalData = {
            skills: {},
            projects: {},
            education: {},
            learning: {}
        };
        this.init();
    }

    /**
     * Initialize modal system
     */
    init() {
        // Create modal container if it doesn't exist
        this.createModalContainer();

        // Load modal data from page
        this.loadModalData();

        // Setup click handlers
        this.setupClickHandlers();

        // Setup keyboard handlers
        this.setupKeyboardHandlers();

        console.log('Modal System initialized');
    }

    /**
     * Create modal container in DOM
     */
    createModalContainer() {
        if (document.getElementById('universal-modal')) return;

        const modalHTML = `
            <div id="universal-modal" class="modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
                <div class="modal-container">
                    <button class="modal-close" aria-label="Close modal">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="modal-content">
                        <!-- Dynamic content goes here -->
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Setup close button
        const closeBtn = document.querySelector('#universal-modal .modal-close');
        closeBtn.addEventListener('click', () => this.closeModal());

        // Setup overlay click
        const overlay = document.getElementById('universal-modal');
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                this.closeModal();
            }
        });
    }

    /**
     * Load modal data from data attributes
     */
    loadModalData() {
        // Skills
        document.querySelectorAll('.skill-card[data-modal]').forEach(card => {
            const data = JSON.parse(card.getAttribute('data-modal'));
            this.modalData.skills[data.name] = data;
        });

        // Projects
        document.querySelectorAll('.project-card[data-modal]').forEach(card => {
            const data = JSON.parse(card.getAttribute('data-modal'));
            this.modalData.projects[data.id] = data;
        });

        // Education
        document.querySelectorAll('.education-list li[data-modal]').forEach(item => {
            const data = JSON.parse(item.getAttribute('data-modal'));
            this.modalData.education[data.id] = data;
        });

        // Learning Goals
        document.querySelectorAll('.learning-list li[data-modal]').forEach(item => {
            const data = JSON.parse(item.getAttribute('data-modal'));
            this.modalData.learning[data.id] = data;
        });
    }

    /**
     * Setup click handlers for all modal triggers
     */
    setupClickHandlers() {
        // Skills
        document.querySelectorAll('.skill-card').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', (e) => {
                e.preventDefault();
                const data = JSON.parse(card.getAttribute('data-modal'));
                this.openSkillModal(data);
            });
        });

        // Projects
        document.querySelectorAll('.project-card').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', (e) => {
                // Don't trigger if clicking on buttons
                if (e.target.closest('.btn') || e.target.closest('.project-links')) {
                    return;
                }
                e.preventDefault();
                const data = JSON.parse(card.getAttribute('data-modal'));
                this.openProjectModal(data);
            });
        });

        // Education
        document.querySelectorAll('.education-list li[data-modal]').forEach(item => {
            item.style.cursor = 'pointer';
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const data = JSON.parse(item.getAttribute('data-modal'));
                this.openEducationModal(data);
            });
        });

        // Learning Goals
        document.querySelectorAll('.learning-list li[data-modal]').forEach(item => {
            item.style.cursor = 'pointer';
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const data = JSON.parse(item.getAttribute('data-modal'));
                this.openLearningModal(data);
            });
        });
    }

    /**
     * Setup keyboard handlers (ESC to close, Tab trap)
     */
    setupKeyboardHandlers() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.currentModal) {
                this.closeModal();
            }
        });
    }

    /**
     * Open skill modal
     */
    openSkillModal(data) {
        const levelText = this.getLevelText(data.level);
        const levelClass = `level-${data.level}`;

        const content = `
            <div class="modal-header">
                <h2><i class="fas fa-code"></i> ${data.name}</h2>
                <span class="skill-level ${levelClass}">${levelText}</span>
            </div>
            <div class="modal-body">
                <div class="modal-section">
                    <h3><i class="fas fa-info-circle"></i> ${this.t('modal_skill_category')}</h3>
                    <p class="category-badge">${this.getCategoryText(data.category)}</p>
                </div>
                
                <div class="modal-section">
                    <h3><i class="fas fa-chart-line"></i> ${this.t('modal_skill_level')}</h3>
                    <div class="skill-progress-modal">
                        <div class="progress-bar" style="width: ${(data.level / 3) * 100}%"></div>
                    </div>
                    <p>${levelText} (${data.level}/3)</p>
                </div>
                
                <div class="modal-section">
                    <h3><i class="fas fa-lightbulb"></i> ${this.t('modal_skill_experience')}</h3>
                    <p>${data.notes}</p>
                </div>
                
                ${data.projects ? `
                <div class="modal-section">
                    <h3><i class="fas fa-folder"></i> ${this.t('modal_skill_projects')}</h3>
                    <ul class="modal-list">
                        ${data.projects.map(project => `<li>${project}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}
            </div>
        `;

        this.showModal(content);
    }

    /**
     * Open project modal
     */
    openProjectModal(data) {
        const content = `
            <div class="modal-header">
                <h2><i class="fas fa-folder-open"></i> ${data.title}</h2>
                ${data.status ? `<span class="project-status status-${data.status}">${this.getStatusText(data.status)}</span>` : ''}
            </div>
            
            ${data.image ? `
            <div class="modal-image">
                <img src="${data.image}" alt="${data.title}" onerror="this.style.display='none'">
            </div>
            ` : ''}
            
            <div class="modal-body">
                <div class="modal-section">
                    <h3><i class="fas fa-align-left"></i> Description</h3>
                    <p>${data.description}</p>
                    ${data.long_description ? `<p class="mt-2">${data.long_description}</p>` : ''}
                </div>
                
                <div class="modal-section">
                    <h3><i class="fas fa-code"></i> ${this.t('modal_project_tech')}</h3>
                    <div class="tech-stack-modal">
                        ${data.tech.map(tech => `<span class="tech-tag">${tech}</span>`).join('')}
                    </div>
                </div>
                
                ${data.features ? `
                <div class="modal-section">
                    <h3><i class="fas fa-star"></i> ${this.t('modal_project_features')}</h3>
                    <ul class="modal-list">
                        ${data.features.map(feature => `<li><i class="fas fa-check"></i> ${feature}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}
                
                <div class="modal-actions">
                    ${data.repo_url ? `
                    <a href="${data.repo_url}" target="_blank" class="btn btn-secondary">
                        <i class="fab fa-github"></i> ${this.t('modal_project_github')}
                    </a>
                    ` : ''}
                    ${data.demo_url ? `
                    <a href="${data.demo_url}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> ${this.t('modal_project_demo')}
                    </a>
                    ` : ''}
                </div>
            </div>
        `;

        this.showModal(content);
    }

    /**
     * Open education modal
     */
    openEducationModal(data) {
        const content = `
            <div class="modal-header">
                <h2><i class="fas fa-graduation-cap"></i> ${data.title}</h2>
            </div>
            
            <div class="modal-body">
                <div class="modal-section">
                    <h3><i class="fas fa-university"></i> ${this.t('modal_education_institution')}</h3>
                    <p>${data.institution || data.title}</p>
                </div>
                
                ${data.period ? `
                <div class="modal-section">
                    <h3><i class="fas fa-calendar"></i> ${this.t('modal_education_period')}</h3>
                    <p>${data.period}</p>
                </div>
                ` : ''}
                
                ${data.description ? `
                <div class="modal-section">
                    <h3><i class="fas fa-info-circle"></i> ${this.t('modal_education_description')}</h3>
                    <p>${data.description}</p>
                </div>
                ` : ''}
                
                ${data.skills ? `
                <div class="modal-section">
                    <h3><i class="fas fa-tasks"></i> ${this.t('modal_education_skills')}</h3>
                    <ul class="modal-list">
                        ${data.skills.map(skill => `<li><i class="fas fa-check"></i> ${skill}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}
                
                ${data.certificate_url ? `
                <div class="modal-actions">
                    <a href="${data.certificate_url}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-certificate"></i> View Certificate
                    </a>
                </div>
                ` : ''}
            </div>
        `;

        this.showModal(content);
    }

    /**
     * Open learning goal modal
     */
    openLearningModal(data) {
        const content = `
            <div class="modal-header">
                <h2><i class="fas fa-target"></i> ${data.title}</h2>
            </div>
            
            <div class="modal-body">
                ${data.description ? `
                <div class="modal-section">
                    <h3><i class="fas fa-info-circle"></i> ${this.t('modal_learning_goal')}</h3>
                    <p>${data.description}</p>
                </div>
                ` : ''}
                
                ${data.progress !== undefined ? `
                <div class="modal-section">
                    <h3><i class="fas fa-chart-line"></i> ${this.t('modal_learning_progress')}</h3>
                    <div class="skill-progress-modal">
                        <div class="progress-bar" style="width: ${data.progress}%"></div>
                    </div>
                    <p>${data.progress}% Complete</p>
                </div>
                ` : ''}
                
                ${data.resources ? `
                <div class="modal-section">
                    <h3><i class="fas fa-book"></i> ${this.t('modal_learning_resources')}</h3>
                    <ul class="modal-list">
                        ${data.resources.map(resource => {
            if (typeof resource === 'object') {
                return `<li><a href="${resource.url}" target="_blank">${resource.name}</a></li>`;
            }
            return `<li>${resource}</li>`;
        }).join('')}
                    </ul>
                </div>
                ` : ''}
                
                ${data.timeline ? `
                <div class="modal-section">
                    <h3><i class="fas fa-calendar-alt"></i> ${this.t('modal_learning_timeline')}</h3>
                    <p>${data.timeline}</p>
                </div>
                ` : ''}
            </div>
        `;

        this.showModal(content);
    }

    /**
     * Show modal with content
     */
    showModal(content) {
        const modal = document.getElementById('universal-modal');
        const modalContent = modal.querySelector('.modal-content');

        // Set content
        modalContent.innerHTML = content;

        // Show modal
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        this.currentModal = modal;

        // Focus trap
        this.setupFocusTrap(modal);

        // Animate in
        requestAnimationFrame(() => {
            modal.querySelector('.modal-container').classList.add('animate-in');
        });
    }

    /**
     * Close modal
     */
    closeModal() {
        const modal = document.getElementById('universal-modal');
        if (!modal || !modal.classList.contains('active')) return;

        // Animate out
        modal.querySelector('.modal-container').classList.remove('animate-in');

        setTimeout(() => {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            this.currentModal = null;
        }, 300);
    }

    /**
     * Setup focus trap for accessibility
     */
    setupFocusTrap(modal) {
        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        if (focusableElements.length === 0) return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        // Focus first element
        firstElement.focus();

        // Trap focus
        modal.addEventListener('keydown', (e) => {
            if (e.key !== 'Tab') return;

            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        });
    }

    /**
     * Helper: Get level text
     */
    getLevelText(level) {
        const levels = {
            1: this.t('skills_level_beginner'),
            2: this.t('skills_level_intermediate'),
            3: this.t('skills_level_advanced')
        };
        return levels[level] || 'Unknown';
    }

    /**
     * Helper: Get category text
     */
    getCategoryText(category) {
        return this.t(`category_${category}`) || category;
    }

    /**
     * Helper: Get status text
     */
    getStatusText(status) {
        return this.t(`modal_project_status_${status}`) || status;
    }

    /**
     * Helper: Translate text
     */
    t(key) {
        return window.translate ? window.translate(key) : key;
    }
}

// Initialize when DOM is ready
let modalSystem;

function initModalSystem() {
    // Wait a bit for language system to initialize
    setTimeout(() => {
        modalSystem = new ModalSystem();
        window.modalSystem = modalSystem;
    }, 100);
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModalSystem);
} else {
    initModalSystem();
}