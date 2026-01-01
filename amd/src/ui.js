
define(['jquery', 'core/ajax', 'core/notification', 'core/str'], function($, Ajax, Notification, Str) {

    var AIButton = {
        init: function(courseId) {
            this.courseId = courseId;
            var triggerElement = $('.course-hero-img .d-flex.align-items-center'); // Hero section selector for RemUI

            if (triggerElement.length) {
                this.renderButton(triggerElement);
            } else {
                // Fallback for standard themes or if RemUI changes
                // Try finding "Bulk Actions" button or "Turn editing on" region
                var fallback = $('.header-actions-container, .page-header-headings');
                if (fallback.length) this.renderButton(fallback);
                else console.warn('[local_topico_ai_proxy] Could not find suitable location for AI Button');
            }
        },

        renderButton: function(container) {
            var btn = $('<button>')
                .addClass('btn btn-primary ml-2 ai-generation-btn')
                .html('✨ <span class="d-none d-md-inline">' + M.str.local_topico_ai_proxy.generate_with_ai + '</span>')
                .attr('type', 'button')
                .on('click', this.handleClick.bind(this));

            container.append(btn);
        },

        handleClick: function(e) {
            e.preventDefault();
            var btn = $(e.currentTarget);
            
            // Confirm dialog
            if (!confirm('Deseja realmente gerar a estrutura do curso com IA? Isso pode alterar tópicos existentes.')) {
                return;
            }

            this.setLoading(btn, true);

            Ajax.call([{
                methodname: 'local_topico_ai_proxy_generate_program',
                args: { courseid: this.courseId }
            }])[0]
            .done(function(response) {
                Notification.alert('Sucesso!', 'A estrutura foi gerada. A página será recarregada.', 'OK', function() {
                     window.location.reload();
                });
            })
            .fail(Notification.exception)
            .always(function() {
                AIButton.setLoading(btn, false);
            });
        },

        setLoading: function(btn, isLoading) {
            if (isLoading) {
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + M.str.local_topico_ai_proxy.generating);
            } else {
                btn.prop('disabled', false).html('✨ <span class="d-none d-md-inline">' + M.str.local_topico_ai_proxy.generate_with_ai + '</span>');
            }
        }
    };

    return {
        init: function(courseId) {
            AIButton.init(courseId);
        }
    };
});
