(function (sx, $, _) {
    sx.createNamespace('classes.favorite', sx);

    sx.classes.favorite._App = sx.classes.Component.extend({
        _init: function () {
            var self = this;

            this.bind('changeFavorite', function (e, data) {
                self.trigger('change', {
                    'Favorite': this
                });
            });
        },

        _onDomReady: function () {
            this.addFavoritesClickHandler();
        },

        addFavoritesClickHandler: function (context) {
            var self = this,
                context = context || document,
                productBtn = $('.sx-favorite-product-btn:not(.bound)', context),
                catalogBtn = $('.sx-favorite-catalog-btn:not(.bound)', context);

            /**
             * Кнопка на странице товара,
             */
            productBtn.addClass('bound').on('click', function (e) {
                var element = $(this),
                    productId = element.data('product-id');

                e.stopPropagation();
                e.preventDefault();

                if (element.hasClass('active')) {
                    productBtn.removeClass('active');
                } else {
                    productBtn.addClass('active');
                }

                self.createAjaxChangeFavorite(productId).execute();
                return this;
            });

            /**
             * Кнопки на странице каталога
             */
            catalogBtn.addClass('bound').on('click', function (e) {
                var element = $(this),
                    productId = element.data('product-id');

                e.stopPropagation();
                e.preventDefault();

                if (element.hasClass('active')) {
                    element.removeClass('active');
                } else {
                    element.addClass('active');
                }

                self.createAjaxChangeFavorite(productId).execute();
                return this;
            });
        },

        /**
         * Расставить всем избранным товарам "сердца"
         */
        showFavorites: function () {
            var ajax = sx.ajax.preparePostQuery(this.get('backend-get-my-favorite'));
            ajax.onSuccess(function (e, data) {
                var products = data.response.data;
                for (var i in products) {
                    var productId = products[i],
                        favoriteProduct = $('.product-item .sx-favorite-catalog-btn[data-product-id="' + productId + '"]');

                    if (favoriteProduct.length > 0) {
                        favoriteProduct.addClass('active');
                    }
                }
            });
            ajax.execute();
        },

        /**
         * @param product_id
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxChangeFavorite: function (product_id) {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-change-favorite'));

            ajax.setData({
                'product_id': product_id
            });

            ajax.onBeforeSend(function (e, data) {
                self.trigger('beforeChangeFavorite', {
                    'product_id': product_id
                });
            });

            ajax.onSuccess(function (e, data) {
                self.set('data', data.response.data);

                self.trigger('changeFavorite', {
                    'product_id': product_id,
                    'response': data.response
                });

                // $.pjax.reload('#favorites-counter-top', {});

                var counterContainer = $('#favorites-counter-top');

                if (counterContainer.length) {
                    $.pjax.reload({container: '#favorites-counter-top', timeout: 5000});
                }
            });

            return ajax;
        }
    });

    sx.classes.favorite.App = sx.classes.favorite._App.extend({});

})(sx, sx.$, sx._);