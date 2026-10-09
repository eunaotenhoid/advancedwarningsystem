(function($) {
    'use strict';

    var AWS = {
        init: function() {
            this.setupToggles();
            this.setupPreviews();
            this.groupRowStyling();
        },

        setupToggles: function() {
            // Find all radio inputs for our extension
            $('input[type="radio"][name^="config[aws_"]').each(function() {
                var $radio = $(this);
                var name = $radio.attr('name');
                
                // Only process the 'Yes' option for initialization
                if ($radio.val() === '1') {
                    if ($radio.data('aws-toggle-init')) return;
                    $radio.data('aws-toggle-init', true);

                    var $noRadio = $('input[type="radio"][name="' + name + '"][value="0"]');
                    var $dd = $radio.closest('dd');
                    
                    // Hide original radios and labels
                    $dd.find('label').addClass('aws-hidden-radio');
                    $radio.addClass('aws-hidden-radio');
                    $noRadio.addClass('aws-hidden-radio');

                    var isActive = $radio.is(':checked');
                    var $toggle = $('<div class="aws-toggle-container' + (isActive ? ' active' : '') + '"><div class="aws-toggle-knob"></div></div>');
                    $dd.append($toggle);

                    $toggle.on('click', function() {
                        if ($toggle.hasClass('active')) {
                            $toggle.removeClass('active');
                            $noRadio.prop('checked', true).trigger('change');
                        } else {
                            $toggle.addClass('active');
                            $radio.prop('checked', true).trigger('change');
                        }
                    });
                }
            });
        },

        setupPreviews: function() {
            var self = this;
            
            // Level 1-5
            for (var i = 1; i <= 5; i++) {
                this.addPreview('aws_level' + i + '_icon', 'aws_level' + i + '_color');
            }
            
            // Banned row
            this.addPreview('aws_banned_icon', 'aws_banned_color');
        },

        addPreview: function(iconId, colorId) {
            var $iconInput = $('#' + iconId);
            var $colorInput = $('#' + colorId);
            
            if (!$iconInput.length) return;

            // Create preview box if it doesn't exist
            var $preview = $('<div class="aws-level-preview" id="preview_' + iconId + '"><i class="fa"></i></div>');
            $colorInput.after($preview);

            var update = function() {
                var icon = $iconInput.val().trim();
                var color = $colorInput.val().trim();
                
                if (color && color.charAt(0) !== '#') {
                    color = '#' + color;
                }
                
                if (icon) {
                    var iconClass = (icon.indexOf('fa-') === -1) ? 'fa-' + icon : icon;
                    $preview.find('i').attr('class', 'fa ' + iconClass + ' fa-fw');
                    $preview.find('i').css('color', color || '#000');
                    $preview.css('border-color', color || '#ddd');
                    $preview.show();
                } else {
                    $preview.hide();
                }
            };

            $iconInput.on('input propertychange change', update);
            $colorInput.on('input propertychange change', update);
            update();
        },

        groupRowStyling: function() {
            // Apply a specific class to the rows that have the appended icon/color fields
            $('.aws-icon-input').closest('dl').addClass('aws-grouped-dl');
            
            // Rules table row
            $('.aws-rules-wrapper').closest('dl').addClass('aws-rules-dl');
        }
    };

    $(function() {
        AWS.init();
    });

})(jQuery);
