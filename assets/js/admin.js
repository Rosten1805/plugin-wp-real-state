/**
 * WP Real Estate - Scripts de administración.
 *
 * @package WPRealEstate
 */

(function ($) {
    'use strict';

    /**
     * Galería de imágenes del inmueble.
     */
    $(document).on('click', '.wpre-gallery__add', function (e) {
        e.preventDefault();

        var $wrapper = $(this).closest('.wpre-gallery');
        var $preview = $wrapper.find('.wpre-gallery__preview');
        var fieldName = $(this).data('field');

        var frame = wp.media({
            title: 'Seleccionar imágenes',
            button: { text: 'Añadir a la galería' },
            multiple: true,
            library: { type: 'image' }
        });

        frame.on('select', function () {
            var attachments = frame.state().get('selection').toJSON();
            attachments.forEach(function (attachment) {
                var thumb = attachment.sizes && attachment.sizes.thumbnail
                    ? attachment.sizes.thumbnail.url
                    : attachment.url;

                $preview.append(
                    '<div class="wpre-gallery__item" data-id="' + attachment.id + '">' +
                    '<img src="' + thumb + '" alt="">' +
                    '<button type="button" class="wpre-gallery__remove">&times;</button>' +
                    '<input type="hidden" name="' + fieldName + '[]" value="' + attachment.id + '">' +
                    '</div>'
                );
            });
        });

        frame.open();
    });

    $(document).on('click', '.wpre-gallery__remove', function (e) {
        e.preventDefault();
        $(this).closest('.wpre-gallery__item').remove();
    });

    /**
     * Selector de archivo genérico (plano de planta, etc.).
     */
    $(document).on('click', '.wpre-file__select', function (e) {
        e.preventDefault();

        var $wrapper = $(this).closest('.wpre-file');
        var fieldId = $(this).data('field');

        var frame = wp.media({
            title: 'Seleccionar archivo',
            button: { text: 'Seleccionar' },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            $wrapper.find('#' + fieldId).val(attachment.id);
            $wrapper.find('.wpre-file__name').text(attachment.filename);
            $wrapper.find('.wpre-file__remove').show();
        });

        frame.open();
    });

    $(document).on('click', '.wpre-file__remove', function (e) {
        e.preventDefault();

        var $wrapper = $(this).closest('.wpre-file');
        var fieldId = $(this).data('field');

        $wrapper.find('#' + fieldId).val('');
        $wrapper.find('.wpre-file__name').text('');
        $(this).hide();
    });

    /**
     * Selector de imagen en Ajustes (logo de la empresa).
     */
    $(document).on('click', '.wpre-media-select', function (e) {
        e.preventDefault();

        var $wrapper = $(this).closest('.wpre-media-field');
        var fieldId = $(this).data('field');

        var frame = wp.media({
            title: 'Seleccionar imagen',
            button: { text: 'Seleccionar' },
            multiple: false,
            library: { type: 'image' }
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            var thumb = attachment.sizes && attachment.sizes.thumbnail
                ? attachment.sizes.thumbnail.url
                : attachment.url;

            $wrapper.find('#' + fieldId).val(attachment.id);
            $wrapper.find('.wpre-media-preview').remove();
            $wrapper.prepend('<img src="' + thumb + '" class="wpre-media-preview" style="max-width:150px;display:block;margin-bottom:8px;">');
            $wrapper.find('.wpre-media-remove').show();
        });

        frame.open();
    });

    $(document).on('click', '.wpre-media-remove', function (e) {
        e.preventDefault();

        var $wrapper = $(this).closest('.wpre-media-field');
        var fieldId = $(this).data('field');

        $wrapper.find('#' + fieldId).val('');
        $wrapper.find('.wpre-media-preview').remove();
        $(this).hide();
    });

    /**
     * Generador de contenido de demostración, ejecutado paso a paso.
     */
    var DemoRunner = {
        steps: [],
        currentStep: 0,
        startTime: 0,

        init: function () {
            if (typeof wpRealEstate === 'undefined' || !wpRealEstate.demoSteps) {
                return;
            }
            this.steps = wpRealEstate.demoSteps;
        },

        start: function () {
            this.currentStep = 0;
            this.startTime = Date.now();

            $('#wpre-demo-actions').hide();
            $('#wpre-demo-panel').show();
            this.resetUI();
            this.renderStepList();
            this.runStep();
        },

        resetUI: function () {
            $('#wpre-progress-bar-fill').css('width', '0%');
            $('#wpre-progress-percent').text('0%');
            $('#wpre-demo-log').empty();
            $('#wpre-progress-status').text('Iniciando...');
            $('#wpre-demo-complete').hide();
        },

        renderStepList: function () {
            var $list = $('#wpre-step-list');
            $list.empty();

            for (var i = 0; i < this.steps.length; i++) {
                var state = 'pending';
                if (i < this.currentStep) state = 'done';
                if (i === this.currentStep) state = 'active';

                $list.append(
                    '<li class="wpre-step wpre-step--' + state + '" data-step="' + i + '">' +
                    '<span class="wpre-step__icon"></span>' +
                    '<span class="wpre-step__label">' + this.steps[i].label + '</span>' +
                    '</li>'
                );
            }
        },

        runStep: function () {
            var self = this;

            if (this.currentStep >= this.steps.length) {
                this.finish();
                return;
            }

            var step = this.steps[this.currentStep];
            var pct = Math.round((this.currentStep / this.steps.length) * 100);

            $('#wpre-progress-bar-fill').css('width', pct + '%');
            $('#wpre-progress-percent').text(pct + '%');
            $('#wpre-progress-status').text(step.label + '...');

            this.renderStepList();

            $.ajax({
                url: wpRealEstate.ajaxUrl,
                type: 'POST',
                timeout: 300000,
                data: {
                    action: 'wpre_generate_demo',
                    nonce: wpRealEstate.nonce,
                    step: this.currentStep
                },
                success: function (response) {
                    if (response.success) {
                        self.logSuccess(response.data);
                        self.currentStep++;
                        self.runStep();
                    } else {
                        self.logError(step.label, response.data.message || 'Error desconocido');
                        self.fail();
                    }
                },
                error: function (xhr, status) {
                    var msg = status === 'timeout' ? 'Tiempo de espera agotado' : 'Error de conexión';
                    self.logError(step.label, msg);
                    self.fail();
                }
            });
        },

        logSuccess: function (data) {
            var $log = $('#wpre-demo-log');
            var $entry = $('<div class="wpre-log-entry wpre-log-entry--success"></div>');
            $entry.append('<span class="wpre-log-entry__icon">&#10003;</span>');
            $entry.append('<span class="wpre-log-entry__text">' + data.message + '</span>');

            if (data.details && data.details.length) {
                var $details = $('<ul class="wpre-log-entry__details"></ul>');
                for (var i = 0; i < data.details.length; i++) {
                    $details.append('<li>' + data.details[i] + '</li>');
                }
                $entry.append($details);
            }

            $log.append($entry);
            $log.scrollTop($log[0].scrollHeight);
        },

        logError: function (label, message) {
            var $log = $('#wpre-demo-log');
            var $entry = $('<div class="wpre-log-entry wpre-log-entry--error"></div>');
            $entry.append('<span class="wpre-log-entry__icon">&#10007;</span>');
            $entry.append('<span class="wpre-log-entry__text">' + label + ': ' + message + '</span>');
            $log.append($entry);
            $log.scrollTop($log[0].scrollHeight);
        },

        finish: function () {
            var elapsed = Math.round((Date.now() - this.startTime) / 1000);
            var minutes = Math.floor(elapsed / 60);
            var seconds = elapsed % 60;
            var timeStr = minutes > 0 ? minutes + ' min ' + seconds + ' s' : seconds + ' s';

            $('#wpre-progress-bar-fill').css('width', '100%');
            $('#wpre-progress-percent').text('100%');
            $('#wpre-progress-status').text('Completado en ' + timeStr);
            this.renderStepList();

            $('#wpre-demo-complete').show()
                .find('.wpre-demo-complete__text')
                .text('Se han generado 8 agentes y 50 inmuebles de demostración en ' + timeStr + '.');

            $('#wpre-demo-complete .button').on('click', function () {
                location.reload();
            });
        },

        fail: function () {
            $('#wpre-progress-status')
                .text('Error en el paso ' + (this.currentStep + 1) + '. Proceso detenido.')
                .addClass('wpre-progress-status--error');

            $('#wpre-demo-retry').show();
        }
    };

    DemoRunner.init();

    $(document).on('click', '#wpre-generate-demo', function () {
        if (!confirm('¿Generar contenido de demostración? Se crearán 8 agentes y 50 inmuebles con imágenes. Este proceso puede tardar varios minutos.')) {
            return;
        }
        DemoRunner.start();
    });

    $(document).on('click', '#wpre-demo-retry .button', function () {
        $('#wpre-demo-retry').hide();
        $('#wpre-progress-status').removeClass('wpre-progress-status--error');
        DemoRunner.runStep();
    });

    /**
     * Eliminar contenido de demostración.
     */
    $(document).on('click', '#wpre-remove-demo', function () {
        var $btn = $(this);

        if (!confirm('¿Eliminar todo el contenido de demostración? Esta acción no se puede deshacer.')) {
            return;
        }

        $btn.prop('disabled', true);
        $('#wpre-demo-actions').hide();
        $('#wpre-demo-panel').show();
        $('#wpre-demo-log').empty();
        $('#wpre-step-list').empty();
        $('#wpre-progress-bar-fill').css('width', '0%');
        $('#wpre-progress-percent').text('');
        $('#wpre-progress-status').text('Eliminando contenido de demostración...');
        $('#wpre-demo-complete').hide();

        $.ajax({
            url: wpRealEstate.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wpre_remove_demo',
                nonce: wpRealEstate.nonce
            },
            success: function (response) {
                if (response.success) {
                    $('#wpre-progress-bar-fill').css('width', '100%');
                    $('#wpre-progress-status').text(response.data.message);
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else {
                    $('#wpre-progress-status').text('Error: ' + response.data.message);
                    $btn.prop('disabled', false);
                }
            },
            error: function () {
                $('#wpre-progress-status').text('Error de conexión.');
                $btn.prop('disabled', false);
            }
        });
    });

})(jQuery);
