jQuery(document).ready(function($) {
    console.log('SLLA Admin Settings JS Loaded');

    // Tabbed Interface Logic
    $('.slla-tab-nav button').on('click', function() {
        const $this = $(this);
        const tabId = $this.data('tab');

        // Update active tab
        $('.slla-tab-nav button').removeClass('active');
        $this.addClass('active');

        // Show corresponding tab content
        $('.slla-tab-content').removeClass('active');
        $('#' + tabId).addClass('active');
    });

    // Show the first tab by default
    $('.slla-tab-nav button:first-child').trigger('click');

    // Getting Started Modal
    $('.slla-getting-started-btn').on('click', function() {
        $('.slla-getting-started-modal').fadeIn();
    });

    $('.slla-close-modal').on('click', function() {
        $('.slla-getting-started-modal').fadeOut();
    });

    // Close modal when clicking outside
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('slla-getting-started-modal')) {
            $('.slla-getting-started-modal').fadeOut();
        }
    });

    // Real-time Error Message Preview for Custom Error Message
    $('#slla_custom_error_message').on('input', function() {
        const defaultMessage = sllaSettings.defaultErrorMessage;
        const previewText = $(this).val().trim() !== '' ? $(this).val() : defaultMessage;
        $('#slla_error_preview').text(previewText);
    });

    // IP Address Validation for Safelist and Denylist
    $('.slla-ip-input').on('input', function() {
        const $this = $(this);
        const ipList = $this.val().trim().split('\n');
        let hasError = false;

        for (let ip of ipList) {
            ip = ip.trim();
            if (ip === '') continue; // Skip empty lines
            // IPv4 validation regex
            const ipRegex = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
            if (!ipRegex.test(ip)) {
                hasError = true;
                break;
            }
        }

        const $errorDiv = $this.siblings('.slla-ip-error');
        if (hasError) {
            $errorDiv.show().text('Invalid IP address detected. Please enter valid IPs (e.g., 192.168.1.1).');
            $this.addClass('slla-input-error');
        } else {
            $errorDiv.hide();
            $this.removeClass('slla-input-error');
        }
    });

    // Prevent Form Submission if IP Validation Fails
    $('form').on('submit', function(e) {
        const $ipInputs = $('.slla-ip-input');
        let hasError = false;

        $ipInputs.each(function() {
            const $this = $(this);
            const $errorDiv = $this.siblings('.slla-ip-error');
            if ($errorDiv.is(':visible')) {
                hasError = true;
            }
        });

        if (hasError) {
            e.preventDefault();
            alert('Please correct the invalid IP addresses before saving.');
        }
    });

    // Premium Feature Validation (e.g., Email Notifications, 2FA)
    $('.slla-premium-checkbox').on('change', function(e) {
        const $this = $(this);
        if ($this.prop('disabled')) {
            e.preventDefault();
            alert('This is a premium feature. Please upgrade to unlock it.');
            window.location.href = sllaSettings.premiumPageUrl; // Redirect to premium page
        }
    });

    // Logs Page Enhancements
    // 1. Confirmation Prompt for Clearing Logs
    $('.slla-clear-btn').on('click', function(e) {
        const confirmClear = confirm('Are you sure you want to clear all logs? This action cannot be undone.');
        if (!confirmClear) {
            e.preventDefault();
        }
    });

    // 2. Add Loading State to Filter Button
    $('.slla-logs-filter-form').on('submit', function() {
        const $filterBtn = $(this).find('.slla-filter-btn');
        $filterBtn.prop('disabled', true).text('Filtering...');
    });

    // 3. Back to Top Button for Logs Page
    if ($('.slla-logs').length) {
        // Append Back to Top button
        $('body').append('<button class="slla-back-to-top">⬆ Back to Top</button>');
        const $backToTopBtn = $('.slla-back-to-top');

        // Show/hide button based on scroll position
        $(window).on('scroll', function() {
            if ($(window).scrollTop() > 300) {
                $backToTopBtn.fadeIn();
            } else {
                $backToTopBtn.fadeOut();
            }
        });

        // Smooth scroll to top
        $backToTopBtn.on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 500);
        });
    }

    // Premium Page Enhancement: Smooth scroll to activation form
    $('.slla-premium-table .slla-upgrade-btn').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $('#slla_setup_code').offset().top - 100
        }, 500);
        $('#slla_setup_code').focus();
    });

    // Real-Time Notifications: Fetch and Update
    if ($('.slla-real-time-notifications').length) {
        function fetchNotifications() {
            $.ajax({
                url: sllaSettings.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'slla_get_recent_failed_attempts',
                    nonce: sllaSettings.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.slla-notification-list').html(response.data.html);
                    }
                },
                error: function() {
                    console.log('Error fetching notifications.');
                }
            });
        }

        // Initial fetch
        fetchNotifications();

        // Poll every 30 seconds
        setInterval(fetchNotifications, 30000);
    }
});