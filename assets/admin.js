/**
 * Ocasio Simple Maintenance Mode - Admin JS
 * Handles Media Library Uploader & Form Feedback
 */
document.addEventListener('DOMContentLoaded', function () {
    // --- SEAMLESS AJAX FORM SUBMIT (Zero Scroll Jumps) ---
    const form = document.getElementById('ocasio-settings-form');
    const saveBtn = document.getElementById('ocasio-save-btn');

    if (form && saveBtn) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            saveBtn.disabled = true;
            saveBtn.innerText = 'Saving...';

            const formData = new FormData(form);
            formData.append('action', 'ocasio_smm_save_settings');
            if (typeof ocasioSmmVars !== 'undefined' && ocasioSmmVars.nonce) {
                formData.append('nonce', ocasioSmmVars.nonce);
            }

            const ajaxUrl = (typeof ocasioSmmVars !== 'undefined' && ocasioSmmVars.ajax_url) ? ocasioSmmVars.ajax_url : form.action;

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                saveBtn.disabled = false;
                saveBtn.classList.add('ko-btn-saved');
                saveBtn.innerText = 'Settings Saved!';

                // Toggle the active banner dynamically
                const enabledCheckbox = form.querySelector('input[name="ocasio_smm_enabled"]');
                const activeBanner = document.getElementById('ocasio-smm-active-banner');
                if (enabledCheckbox && activeBanner) {
                    activeBanner.style.display = enabledCheckbox.checked ? 'flex' : 'none';
                }

                setTimeout(() => {
                    saveBtn.classList.remove('ko-btn-saved');
                    saveBtn.innerText = 'Save Settings';
                }, 2000);
            })
            .catch(err => {
                saveBtn.disabled = false;
                saveBtn.innerText = 'Save Settings';
            });
        });
    }

    // --- MEDIA UPLOADER & LOGO PREVIEW ---
    var mediaUploader;
    const uploadBtn = document.getElementById('ocasio-smm-logo-btn');
    const removeBtn = document.getElementById('ocasio-smm-remove-logo-btn');
    const urlInput = document.getElementById('ocasio-smm-logo');
    const previewWrap = document.getElementById('ocasio-smm-logo-preview-wrap');
    const previewImg = document.getElementById('ocasio-smm-logo-preview');

    if (uploadBtn && urlInput) {
        uploadBtn.addEventListener('click', function (e) {
            e.preventDefault();

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            if (typeof wp === 'undefined' || !wp.media) {
                return;
            }

            mediaUploader = wp.media({
                title: 'Select Logo',
                button: {
                    text: 'Use this Logo'
                },
                multiple: false
            });

            mediaUploader.on('select', function () {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                urlInput.value = attachment.url;
                if (previewImg && previewWrap) {
                    previewImg.src = attachment.url;
                    previewWrap.style.display = 'flex';
                }
                if (removeBtn) {
                    removeBtn.style.display = 'inline-flex';
                }
            });

            mediaUploader.open();
        });
    }

    if (removeBtn && urlInput) {
        removeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            urlInput.value = '';
            if (previewWrap) {
                previewWrap.style.display = 'none';
            }
            if (previewImg) {
                previewImg.src = '';
            }
            removeBtn.style.display = 'none';
        });
    }

    // Sync input typing with preview
    if (urlInput) {
        urlInput.addEventListener('input', function () {
            const val = urlInput.value.trim();
            if (val && previewImg && previewWrap) {
                previewImg.src = val;
                previewWrap.style.display = 'flex';
                if (removeBtn) {
                    removeBtn.style.display = 'inline-flex';
                }
            } else if (!val) {
                if (previewWrap) {
                    previewWrap.style.display = 'none';
                }
                if (removeBtn) {
                    removeBtn.style.display = 'none';
                }
            }
        });
    }

    // --- RESET DEFAULT TEXT ---
    const resetBtn = document.getElementById('ocasio-smm-reset-btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const headlineInput = document.querySelector('input[name="ocasio_smm_headline"]');
            const messageInput = document.querySelector('textarea[name="ocasio_smm_message"]');
            const logoInput = document.getElementById('ocasio-smm-logo');
            const logoWidthInput = document.getElementById('ocasio-smm-logo-width');

            if (headlineInput) {
                headlineInput.value = "We're Building Something Great";
            }
            if (messageInput) {
                messageInput.value = "Our site is currently undergoing scheduled maintenance. Please check back soon.";
            }
            if (logoInput) {
                logoInput.value = '';
            }
            if (logoWidthInput) {
                logoWidthInput.value = '260';
            }
            if (previewWrap) {
                previewWrap.style.display = 'none';
            }
            if (previewImg) {
                previewImg.src = '';
            }
            if (removeBtn) {
                removeBtn.style.display = 'none';
            }

            resetBtn.innerText = 'Default Text Restored!';
            setTimeout(() => {
                resetBtn.innerText = 'Reset Default Text';
            }, 2000);
        });
    }

    // --- SUITE AJAX TOGGLE LISTENER (for Dashboard view) ---
    document.querySelectorAll('.ko-suite-ajax-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const optionName = this.getAttribute('data-option');
            const slug = this.getAttribute('data-slug');
            const isChecked = this.checked ? '1' : '0';
            const badge = document.getElementById('badge-' + slug);

            const data = new FormData();
            data.append('action', 'ocasio_suite_save_toggle');
            data.append('option', optionName);
            data.append('value', isChecked);
            const nonce = (typeof ocasio_vars !== 'undefined' && ocasio_vars.suite_nonce) ? ocasio_vars.suite_nonce : ((typeof ocasioSmmVars !== 'undefined' && ocasioSmmVars.suite_nonce) ? ocasioSmmVars.suite_nonce : '');
            data.append('nonce', nonce);

            const ajaxUrl = (typeof ajaxurl !== 'undefined') ? ajaxurl : ((typeof ocasioSmmVars !== 'undefined' && ocasioSmmVars.ajax_url) ? ocasioSmmVars.ajax_url : '/wp-admin/admin-ajax.php');

            fetch(ajaxUrl, {
                method: 'POST',
                body: data
            })
            .then(res => res.json())
            .then(res => {
                if (res.success && badge) {
                    if (isChecked === '1') {
                        badge.className = 'ko-dash-badge badge-active';
                        badge.textContent = 'Active';
                    } else {
                        badge.className = 'ko-dash-badge badge-paused';
                        badge.textContent = 'Paused';
                    }
                }
            });
        });
    });
});
