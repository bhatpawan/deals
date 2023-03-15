/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Backend/js/media-uploader',
    'Magento_Catalog/js/product-gallery'
], function ($, $t, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            images: [],
            imageTypes: [],
            imagePlaceholder: $t('Browse to find or drag image here'),
            deleteButtonLabel: $t('Delete image'),
            deleteConfirmMessage: $t('The image will be deleted from current scope. Do you want to proceed?'),
            imageFadeLabel: $t('Hidden'),
            uploader: {
                url: '',
                fileField: 'image',
                placeholder: $t('Browse Files...'),
                config: {
                    maxFileSize: 8388608,
                    maxWidth: 1920,
                    maxHeight: 1080,
                    isResizeEnabled: 1
                }
            },
            dialog: {
                label: $t('Label'),
                sizeLabel: $t('Image Size'),
                resolutionLabel: $t('Image Resolution')
            }
        },
        afterGalleryTemplateRendered: function (object) {
            $('#' + this.uid + '-uploader').mediaUploader(
                this.uploader.config
            );
            var self = this;
            var gallery = $('#' + this.uid);
            gallery.productGallery($.extend({}, {
                template: '#' + this.uid + '-template',
            }, {}));
            gallery.off('mouseup', '[data-role=delete-button]');
            gallery.on('click', '[data-role=delete-button]', function (event) {
                var $imageContainer;

                event.preventDefault();
                event.stopImmediatePropagation();
                $imageContainer = $(event.currentTarget).closest('[data-role=image]');
                gallery.find('[data-role=dialog]').trigger('close');
                if (confirm(self.deleteConfirmMessage)) {
                    gallery.trigger('removeItem', $imageContainer.data('imageData'));
                }
            });

        }
    });
});
