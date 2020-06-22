var DataLayer = {

  devMode: true,
  currencyCodeRub: 'RUB',
  actionName: {
    product: {
      impressions: 'Product Impressions',
      click: 'Product Click',
      detail: 'Product Detail',
    }
  },
  eventName: {
    ecEvent: 'ecEvent',
    gaEvent: 'gaEvent',
  },
  categoryName: {
    eCommerce: 'Ecommerce'
  },
  selectors: {
    base: '.data-layer',
    btnAddToCart: '.data-layer_add-in-cart',
    productDetail: '.data-layer_product-detail',
    productQuickView: '.data-layer_quick-view',
    impression: '.data-layer_impression',
    impressionCard: '.data-layer_impression_card',
    promo: '.data-layer_promo',
  },
  dataAttributeName: 'data_layer',

  init: function () {

    window.dataLayer = window.dataLayer || [];
    this.productDetail();
    this.productImpressions();

    if (!this.cart) {
      this.cart = new DataLayerCart(this.selectors);
    }

    if (!this.promo) {
      this.promo = new DataLayerPromo(this.selectors);
    }

    $('body').on('click', '.data-layer_click', function (e) {
      var $el = $(this);
      var eventData = $el.data('datalayer');
      if (eventData) {
        window.dataLayer.push(eventData);
      }
    });
  },
  getCart: function () {
    if (!this.cart) {
      this.cart = new DataLayerCart(this.selectors);
    }
    return this.cart;
  },
  /**
   *
   */
  openCatalog: function () {

    var impressions = this.findProductImpressions();
    if (impressions) {

      var ids = [];
      var priceSum = 0;
      impressions.forEach((impressionList) => {
        if (impressionList.length) {
          impressionList.forEach(impression => {
            ids.push(impression.id);
            priceSum += impression.price
          });
        }
      });

      dataLayer.push({
        'event':      'google_tag_params',
        'id':          ids, //массив ID отображаемых товаров
        'pageType':   'catalog',
        'totalValue': priceSum,
      });
    }
  },

  openProduct: function (product) {

    dataLayer.push({
      'event':      'google_tag_params',
      'id':         product.id,
      'pageType':   'product',
      'totalValue': product.price,
    });
  },

  openCart: function () {
    if (!this.cart) {
      this.cart = new DataLayerCart(this.selectors);
    }
    this.cart.open();
  },
  viewPromo: function () {
    if (!this.promo) {
      this.promo = new DataLayerPromo(this.selectors);
    }
    this.promo.init();
  },

  /**
   * Для зарегистрированного пользователя при заходе на сайт
   * @param {String} userID - Идентификатор пользователя из внутренней системы'
   * @param {String} userEmail - E-mail пользователя, если он есть
   * @param {String} userTel - Телефон пользователя, если он есть
   * @param {String} userName - ФИО пользователя, если они указаны
   */
  userLogin(userID, userEmail, userTel, userName) {
    dataLayer = dataLayer || [];
    dataLayer.push({
      'user': {
        userID,
        userEmail,
        userTel,
        userName,
      },
    });
  },

  /**
   * При отправке любой формы с полем emaйл или телефон
   * @param {String} action - Название формы
   * @param {String} label - Телефон или емайл | значение меняется в зависимости от типа пароля, для телефона значение имет вид tel_89001234567, для емайл вид email_test@test.ts
   */
  onSubmitEmailOrPhoneForm(action, label) {
    dataLayer.push({
      'event': 'gaEvent',
      'eventdata': {
        'category': 'SendForm',
        action,
        label,
        'ni': false,
      },
    });
  },

  /**
   * Формируем gaEvent
   *
   * @param {String} eventName
   * @param {Object}  eCommerce
   * @param {String}  action
   * @param {Boolean}  ni
   * @returns {{eventdata: {action: *, ni: *, category: string}, ecommerce: *, event: string}}
   */
  gaEventPopulate: function (eventName, eCommerce, action, ni) {

    var event = {
      'event': eventName,
      'eventdata': {
        'category': this.categoryName.eCommerce,
        'action': action,
        'ni': ni || false
      },
    };

    if (eCommerce) {
      event.ecommerce = eCommerce
    }

    return event;
  },
  /**
   * Показы товара
   */
  productImpressions: function (impressions) {
    var action = this.actionName.product.impressions;

    //get default value for impressions
    if (!impressions) {
      impressions = this.findProductImpressions();
    }

    if (impressions) {

      impressions.forEach((impression) => {
        var eCommerce = {
          'currencyCode': this.currencyCodeRub,
          'impressions': impression
        };
        dataLayer.push(this.gaEventPopulate(this.eventName.gaEvent, eCommerce, action, true));
      });
    }
  },
  /**
   * Клики по товарам
   * @param product
   */
  productClick: function (product) {

    var action = this.actionName.product.click;
    var eCommerce = {
      'currencyCode': this.currencyCodeRub,
      'click': {
        'actionField': {'list': product.list}, // Список в котором идет показ
        'products': [product]
      }
    };

    dataLayer.push(this.gaEventPopulate(this.eventName.gaEvent, eCommerce, action, false));
  },
  /**
   * Клики в товарах по фильтрам модификаций.
   * @param cardId
   */
  changeCard: function (cardId) {
    var product = $(this.selectors.productDetail).data(this.dataAttributeName);

    if (product.id != cardId) {
      product.id = cardId;

      var $modification = $('.product_size.modification-property.active');

      if ($modification) {
        product.dimension8 = $modification.text();
        product.variant = $('.colors-list .modification-property.active').attr('title') || '';
      }

      this.productDetail(product);

      dataLayer.push({
        'event':      'google_tag_params',
        'id':         product.id,
        'pageType':   'product',
        'totalValue': product.price,
      });
    }

  },

  /**
   * Клики по "быстрый промотр"
   * @param product
   */
  quickViewClick: function (product) {
    //todo:: слелать получение инфы продуката по клику, а так же не хватает прослушивания клика
    var action = this.actionName.product.click;
    var eCommerce = {
      'currencyCode': this.currencyCodeRub,
      'click': {
        'actionField': {'list': product.list}, // Список в котором идет показ
        'products': [product]
      }
    };

    dataLayer.push(this.gaEventPopulate(this.eventName.gaEvent, eCommerce, action, false));
    this.productDetail(product);

    $('body').on('click', this.selectors.impressionCard, function(e) {
      //data-data_layer
      var $el = $(this);
      var value = DataLayer.getValueBySelector($el);

      if(!value.index) {
        value.index = $el.index()
      }
      DataLayer.productClick(value);
    });
  },
  /**
   * //todo:: пока что "Избранное" отсутствует на сайте.
   * Избранное
   * - addFavorite – если товар был добавлен в Избранное
   *  removeFavorite – если товар был удален из Избранного
   */
  toggleFavorite: function () {
    const example = {
        'event': 'gaEvent',
        'eventdata': {
          'category': 'Ecommerce',
          'action': '%Статус Действия%',
          'ni': false
        }
      }
    ;

    var action = this.actionName.product.click;
    var eCommerce = {
      'currencyCode': this.currencyCodeRub,
      'click': {
        'actionField': {'list': product.list}, // Список в котором идет показ
        'products': [product]
      }
    };

    dataLayer.push(this.gaEventPopulate(this.eventName.gaEvent, eCommerce, action, false));
  },
  /**
   * Открытие карточки товара
   * !Внимание! Также данный код необходимо исполнять при открытии
   * окна товара после клика по кнопке «Быстрый просмотр».
   * @param product
   */
  productDetail: function (product) {

    if (!product) {
      product = $(this.selectors.productDetail).data(this.dataAttributeName);
    }

    if (product) {
      if (product.position) {
        delete product.position
      }
      var action = this.actionName.product.detail;
      var eCommerce = {
        'currencyCode': this.currencyCodeRub,
        'detail': {
          'products': [product]
        }
      };
      dataLayer.push(this.gaEventPopulate(this.eventName.gaEvent, eCommerce, action, true));
    }
  },
  /**
   * Поиск данных для "Показы товара"
   * @returns {*|Array}
   */
  findProductImpressions: function (container, limit) {

    var items = [];
    var elements;

    if (container) {
      elements = container.find(this.selectors.base + this.selectors.impression);
    } else {
      elements = $(this.selectors.base + this.selectors.impression);
    }

    if (elements) {
      $.each(elements, (i, el) => {

        var $el = $(el);
        var value = this.getValueBySelector(el);

        if (value) {
          value.position = ++i;

          $el.unbind('click');
          $el
            .on('click', 'a:not(.data-layer_quick-view)', () => {
              this.productClick(value);
            })
            .on('click', 'a.data-layer_quick-view', (e) => {
              var quickViewValue = this.getValueBySelector(e.target);
              if (quickViewValue) {
                quickViewValue.position = value.position;
                quickViewValue.list = value.list;
                this.quickViewClick(quickViewValue);
              }
            });
          items.push(value);
        }
      });

      if (!limit) {
        limit = 10;
      }

      return this.arrayChunk(items, limit);
    }

  },
  lazyLoadProducts: function (container) {
    var impressions = this.findProductImpressions(container);

    if (impressions) {
      this.productImpressions(impressions);
    }
  },
  /*** Utils **/
  /**
   * Разбивка массива на равные части
   * @param array
   * @param limit
   * @returns {Array}
   */
  arrayChunk: function (array, limit = 10) {

    if (!limit) {
      limit = 10;
    }

    var i, j, tmp = [];
    for (i = 0, j = array.length; i < j; i += limit) {
      tmp.push(array.slice(i, i + limit));
    }
    return tmp;
  },

  preventDefault: function (e) {
    if (this.devMode) {
      e.preventDefault();
      return false;
    }
  },
  getValueBySelector: function (selcector) {
    var $el = selcector.jquery ? selcector : $(selcector);
    if ($el && $el.length) {
      var value = $el.data(this.dataAttributeName);
      if (value && $el) {
        value.position = $el.index();
      }
      return value;
    }
    return null;
  }

};

/**
 * DataLayer for Cart events
 * @param {Object} selectors
 * @constructor
 */
function DataLayerCart(selectors) {

  //.user-dashboard .content-body .orders-ordering .form-container .col-main .form-group .control-label
  this.actionLabels = {
    open: "Open Cart",
    add: "Add to Cart",
    purchase: "Purchase",
    remove: "Remove from Cart",
    productClick: "Product Click",
    inputEmail: 'Email',
    inputPhone: 'Telephone',
    promoCodTrue: 'PromoCodTrue',// если промокод был веден корректный
    promoCodFalse: 'PromoCodFalse',// если промокод применить невозможно
  };

  this.eventLabels = {
    ecEvent: 'ecEvent',
    gaEvent: 'gaEvent',
  };

  this.currencyCodeRub = 'RUB';
  this.dataAttributeName = 'data_layer';
  this.selectors = selectors;
  this.selectors.cartProduct = '.data-layer_cart-product';
  this.selectors.quantityControl = '.quantity-control';
  this.selectors.quantityValue = '.quantity-value';
  this.selectors.quantityPlus = '.btn.control-less';
  this.selectors.quantityMinus = '.btn.control-less';
  this.selectors.retailRocketWidget = {
    container: '.rr-widget-cart-rel.rr-active',
    containerCard: '.rr-widget-card-rel.rr-active',//Сопутствующие товары
    title: '.widgettitle',
    item: {
      container: '.rr-item',
      title: '.item-info .item-title',
      quickView: '.ss-open-quickview',
      price: '.item-price .item-price-value'
    },
  };

  setTimeout(function () {
    this.product.retailRocketWidget()
  }.bind(this), 5000);

  /**
   * Получем значение дата атрибута по селектору Dom - элемента
   * @param {String} el
   * @returns {null|Object}
   */
  this.getAttributes = function (el) {
    var $el = $(el);
    return $el.length ? $el.data(this.dataAttributeName) : null
  };

  this.getQuantityControlSelectorName = function (selector) {
    return this.selectors.cartProduct + ' ' + selector;
  };

  var
    selectorForMinus = this.getQuantityControlSelectorName('.control-less'),
    selectorForPlus = this.getQuantityControlSelectorName('.control-more'),
    selectorForDelete = this.getQuantityControlSelectorName('.control-del');

  $('body')
    .on('click', selectorForMinus, (e) => {
      var value = this.getValueBySelector(null, $(e.target).closest(this.selectors.cartProduct));
      if (value) {
        value.quantity = 1;
        this.product.minus(value);
      }
    })
    .on('click', selectorForPlus, (e) => {
      var value = this.getValueBySelector(null, $(e.target).closest(this.selectors.cartProduct));
      if (value) {
        value.quantity = 1;
        this.product.plus(value);
      }
    })
    .on('click', selectorForDelete, (e) => {
      var value = this.getValueBySelector(e.target);
      if (value) {
        this.product.delete(value);
      }
    })
    .on('blur', '#input-promo-code', (e) => {
      this.input.promoCode($(e.target));
    })
    .on('blur', '#quickorder-phone', (e) => {
      this.input.phone($(e.target));
    })
    .on('blur', '#quickorder-email', (e) => {
      this.input.email($(e.target));
    });


  /**
   * Открытие корзины
   * Примечание: Если посетитель сайта, не добавил в корзину товар
   * и при этом заходит на страницу Корзины, то необходимо сделать так,
   * чтобы код описанный ниже не исполнялся.
   */
  this.open = function () {

    var impressions = this.product.findAll();

    var ids = [];
    var cardIds = [];
    var priceSum = 0;

    if (impressions.length) {
      impressions.forEach((impressionList) => {

        if (impressionList.length) {

          impressions.forEach((impressionList) => {
            if (impressionList.length) {
              impressionList.forEach((impression) => {

                ids.push(impression.variant);
                cardIds.push(impression.id);

                var cost = impression.price;

                if (typeof cost === 'string') {
                  cost = parseFloat(cost.replace(/\s+/g, ''));
                }

                var count = parseInt(impression.quantity);

                if (isNaN(count)) {
                  count = 1;
                }

                priceSum += cost * count;
              });
            }
          });

        }

        // не забываем бить продукты по 10
        var eCommerce = {
          'currencyCode': this.currencyCodeRub,
          'checkout': {
            'actionField': {'step': 1},
            'products': impressionList
          }
        };
        dataLayer.push(DataLayer.gaEventPopulate(this.eventLabels.gaEvent, eCommerce, this.actionLabels.open, true));
      });

      dataLayer.push({
        'event':      'google_tag_params',
        'id':         cardIds,
        'variant':    ids,
        'pageType':   'cart',
        'totalValue': priceSum,
      });

    }
  };

  /**
   * открытие стр финиш
   * @param data
   */
  this.finish = function (data) {

    var sentOrderId = parseInt(localStorage.getItem('ga_sent_for_order'), 10);

    if (isNaN(sentOrderId) || parseInt(data.order.id, 10) !== sentOrderId) {
      var event = {
        'event': this.eventLabels.gaEvent,
        'eventdata': {
          'category': 'Ecommerce',
          'action': this.actionLabels.purchase,
          'ni': true
        },
        'order': data.order,
        'ecommerce': {
          'currencyCode': this.currencyCodeRub,
          'purchase': {
            'actionField': {
              'id': data.order.id,  // идентификатор транзакции
              'affiliation': 'shopandshow.ru',  // сайт
              'revenue': data.order.revenue,  // стоимость заказа (включает в себя налог и доставку)
              'tax': data.order.tax, // налог (сервисный сбор)
              'shipping': data.order.shipping, // стоимость доставки
              'coupon': data.order.coupon //купон (подарочная карта), если используется
            },
            'products': data.products
          }
        }
      };
      dataLayer.push(event);
      localStorage.setItem('ga_sent_for_order', data.order.id);

      var ids = {}, cardIds = {}, priceSum = 0;
      if (data.products) {
        data.products.forEach((product) => {
          ids[product.id] = null;
          cardIds[product.variant] = null;
          var cost = typeof product.price === "string" ? parseFloat(product.price.replace(/\s+/g, '')) : product.price;
          var count = parseInt(product.quantity);
          priceSum += (cost * count);
        })
      }

      dataLayer.push({
        'event':      'google_tag_params',
        'id':         Object.keys(ids),
        'variant':    Object.keys(cardIds),
        'pageType':   'purchase',
        'totalValue': priceSum,
      })
    }
  };

  this.product = {
    /**
     * Добавление товара в корзину
     * @param {Object} attributes
     */
    add: function (attributes) {
      if (!attributes) {
        attributes = this.getAttributes(this.selectors.productDetail);
        if (!attributes) {
          return
        }
        attributes.quantity = 1;
        var $modification = $('.product_size.modification-property.active');

        if ($modification) {
          attributes.id = $(this.selectors.btnAddToCart).data('product_id');
          if (!attributes.dimension8 || attributes.dimension8 === '') {
            attributes.dimension8 = $modification.text();
          }
          if (!attributes.variant || attributes.variant === '') {
            attributes.variant = $('.colors-list .modification-property.active').attr('title') || '';
          }
        }
      }
      if (!attributes) {
        return
      }
      var action = this.actionLabels.add;

      var eCommerce = {
        'currencyCode': this.currencyCodeRub,
        'add': {
          'products': [attributes]
        }
      };

      var event = DataLayer.gaEventPopulate(this.eventLabels.gaEvent, eCommerce, action, false);
      dataLayer.push(event);

    }.bind(this),

    /**
     * Клики по товарам в Корзине
     */
    click: function (attributes, list) {

      var eCommerce = {
        'currencyCode': this.currencyCodeRub,
        'click': {
          'actionField': {'list': list},
          'products': [attributes]
        }
      };

      var event = DataLayer.gaEventPopulate(this.eventLabels.gaEvent, eCommerce, this.actionLabels.productClick, false);
      dataLayer.push(event);

    }.bind(this),

    /**
     * Удаление (уменьшение кол-ва) товара из корзины
     * При удалении товара из корзины с помощью кнопки «Удалить» или кнопки «Минус» необходимо исполнять JavaScript код вида:
     */
    minus: function (product) {
      product.quantity = 1;

      var eCommerce = {
        'remove': {
          'currencyCode': this.currencyCodeRub,
          'products': [product]
        }
      };
      var event = DataLayer.gaEventPopulate(this.eventLabels.gaEvent, eCommerce, this.actionLabels.remove, false);
      dataLayer.push(event);

    }.bind(this),

    /**
     * Увеличение кол-ва товара в корзине
     */
    plus: function (product) {
      this.product.add(product);
    }.bind(this),

    findAll: function () {
      var items = [];

      $.each($(this.selectors.cartProduct), (i, el) => {
        var value = this.getValueBySelector(el);

        if (value) {
          items.push(value);
        }

      });

      return DataLayer.arrayChunk(items, 10);
    }.bind(this),

    /**
     * С этим товаром покупают
     */
    retailRocketWidget: function () {

      // console.log('retailRocketWidget');
      var isProductPage = window.location.href.indexOf('/products/') !== -1;

      ['.rr-widget', this.selectors.retailRocketWidget.container, this.selectors.retailRocketWidget.containerCard].forEach((selector) => {

        // console.log(selector);
        var $widget = $(selector);
        var title = this.product.getValueFromElementText($widget.find(this.selectors.retailRocketWidget.title));
        var itemSelectors = this.selectors.retailRocketWidget.item;
        var impressions = [];

        $.each($widget.find(itemSelectors.container+":not(.slick-cloned)"), function (i, el) {

          var $item = $(el);
          var $quickView = $item.find(itemSelectors.quickView);
          var list = isProductPage ? ($('h1.product-name').text() +  ' - ' + title) : ('Корзина - ' + title);

          var value = {
            'id': $quickView.data('product-id'), // Идентификатор/артикул товара
            'name': this.product.getValueFromElementText($item.find(itemSelectors.title)), // название товара
            'price': this.product.getValueFromElementText($item.find(itemSelectors.price)).replace(/\s+/g, ''), // Стоимость за единицу товара
            'list': list,
            'position': ++i, //Позиция товара в списке показа
            'brand': '', // Торговая марка
            'category': '', // Дерево категорий, где в качестве разделителя используется символом косой черты «/». Можно указать до пяти уровней иерархии.
          };

          if (typeof value.price === 'string') {
            value.price = parseFloat(value.price);
          }

          $item.unbind('click');
          $item
            .on('click', 'a:not(.data-layer_quick-view)', () => {
              this.product.click(value, list);
            });

          $quickView.unbind('click');
          $quickView.on('click', (e) => {
            DataLayer.quickViewClick(value);
          });
          impressions.push(value);

        }.bind(this));

        DataLayer.productImpressions(DataLayer.arrayChunk(impressions));
      });


    }.bind(this),

    /**
     * Получаем значение из текста элемента
     * @param $el
     * @returns {*}
     */
    getValueFromElementText: function ($el) {
      return $el.length ? $el.text().trim() : null;
    }
  };

  this.input = {

    /**
     * Ввод e-mail
     */
    email: function ($input) {

      if (this.input.isValid($input)) {
        var event = DataLayer.gaEventPopulate(this.eventLabels.gaEvent, null, this.actionLabels.inputEmail, false);
        dataLayer.push(event);
      }
    }.bind(this),

    /**
     * Ввод номера телефона
     */
    phone: function ($input) {

      if (this.input.isValid($input)) {
        var event = DataLayer.gaEventPopulate(this.eventLabels.gaEvent, null, this.actionLabels.inputPhone, false);
        dataLayer.push(event);
      }
    }.bind(this),

    /**
     * Промокод
     * При успешной отправки формы с промокодом (взаимодействие с формой «Промокод»)
     */
    promoCode: function ($input) {
      // for example

      if (this.input.isValid($input)) {

        var successfullyInputPromoCode = false;

        var action = successfullyInputPromoCode ? this.actionLabels.promoCodTrue : this.actionLabels.promoCodFalse;
        var event = DataLayer.gaEventPopulate(this.eventLabels.gaEvent, null, action, false);

        dataLayer.push(event);

      }

    }.bind(this),
    isValid: function ($input) {
      return $input.val().length && !$input.closest('.form-group').hasClass('has-error')
    }

  };

  /**
   *
   * @param selector
   * @param el
   * @returns {Object|null}
   */
  this.getValueBySelector = function (selector, el) {

    var value = DataLayer.getValueBySelector(selector || el);

    if (value) {
      var $el = selector ? $(selector) : el;

      var $count = $el.find(this.selectors.quantityControl + ' ' + this.selectors.quantityValue);
      if ($count.length) {
        var count = parseInt($count.data('value'), 10);
        value.quantity = isNaN(count) ? 1 : count; // Количество товара в корзине
      }

      if (!value.size || value.size === '') {
        value.size = $el.find('.product-prop.product-prop-size .prop-val').text();

        if (value.size) {
          value.size = parseInt(value.size.trim());
        }

      }
      if (!value.variant || value.variant === '') {
        value.variant = $el.find('.product-prop.product-prop-color .prop-val').attr('title');
      }
    }
    if (value.position) {
      delete value.position;
    }
    return value;
  }
}

function DataLayerPromo(selectors) {
  this.eventLabels = {
    ecEvent: 'ecEvent',
    gaEvent: 'gaEvent',
  };
  this.actionLabels = {
    click: "Promotion Click",
    view: "Promotion View",
  };
  this.selectors = selectors;

  this.init = function () {

    var promotions = this.findAll();

    if (promotions.length) {
      promotions.forEach(function (promoList) {
        var eCommerce = {
          'currencyCode': DataLayer.currencyCodeRub,
          'checkout': {
            'actionField': {'step': 1},
            'products': promoList
          }
        };
        dataLayer.push(DataLayer.gaEventPopulate(this.eventLabels.gaEvent, eCommerce, this.actionLabels.view, true));
      }.bind(this));
    }
  };

  /**
   * Клики по промо
   * @param promo
   */
  this.click = function (promo) {

    var action = this.actionLabels.click;
    var eCommerce = {
      'currencyCode': DataLayer.currencyCodeRub,
      'promoClick': {
        'promotions': [promo]
      }
    };
    dataLayer.push(DataLayer.gaEventPopulate(this.eventLabels.gaEvent, eCommerce, action, false));
  };

  /**
   * Поймать все промо
   * @param container
   * @param limit
   * @returns {*|Array}
   */
  this.findAll = function (container, limit) {
    var items = [];
    var elements;
    var selector = this.selectors.base + this.selectors.promo + ":not(.swiper-slide-duplicate)";
    if (container) {
      elements = container.find(selector);
    } else {
      elements = $(selector);
    }

    //on click

    var dataLayerPromo = this;
    $('body').on('click', this.selectors.base + this.selectors.promo, function (e) {

      var value = dataLayerPromo.getValueBySelector($(this));
      if (value && !value.position) {
        value.position = $(this).index();
      }
      dataLayerPromo.click(value);
    });

    if (elements) {
      var position = 0;
      $.each(elements, (i, el) => {
        var value = this.getValueBySelector(el);
        if (value && !value.position) {
          value.position = ++position;
        }
        items.push(value);
      });
      return DataLayer.arrayChunk(items, 10);
    }

  };

  /**
   * Получаем из data-attribute json - данные
   * @param selector
   * @returns {null|*}
   */
  this.getValueBySelector = function (selector) {

    var $el = selector.jquery ? selector : $(selector);
    if ($el && $el.length) {
      var value = $el.data(DataLayer.dataAttributeName);
      // console.log($el);
      if (value) {
        value.position = $el.index();
      }
      return value;
    }
    return null;
  };
}

if (!String.prototype.trim) {
  (function () {
    // Вырезаем BOM и неразрывный пробел
    String.prototype.trim = function () {
      return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
    };
  })();
}
