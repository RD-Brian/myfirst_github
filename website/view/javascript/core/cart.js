var cart = {
      'add':function (product_id) {
          var quantity = $('input[name=\'product_quantity\']').val();
          if(!quantity){
            var quantity = 1;
          }
          if(quantity > 100){
            alert('超過購物車設限');
            return;
          }
          $.ajax({
            url: '{{ check_cart }}',
            type: 'post', 
            data: 'product_id=' + product_id + '&quantity=' + quantity,
            dataType: 'json',

            success:function(json) {
              if(json['status'] == 'success'){
                  $('.items_total').html(json['total']);
                  $('.cart-list').load('index.php?route=widget/cart/info .cart-list-box');
               }
              alert(json['msg']);
              // //無庫存
              // if (json['error'] && is_turn != true){
              //   alert(json['error']);
              // } else if(is_turn == true) {
              //   location.href = '{{ gocheckcart }}';
              // }
           }
        });

      },
      'remove':function (cart_id) {
          $.ajax({
            url: 'index.php?route=ajax/cart/remove',
            type: 'post',
            data: 'cart_id=' + cart_id,
            dataType: 'json',
           success: function(json){

            switch(json['status']) {
                case 'success':

                $('.items_total').html(json['cart_count']);
                $('.cart-list').load('index.php?route=widget/cart/info .cart-list-box');
                // $('.reload-price').load('index.php?route=checkout/cart ._reload-price');
                
                break;

                case 'warning':
              //   if(location.href == '{{ view_cart }}' || location.href == '{{ _checkout }}'){
              //   if(json['total'] == 0) {
                  
              //     location.href='{{ product_category }}';
              //   }
              // }
                break;
            }
            alert(json['message']);
        }
        });
      },
      'update':function (product_id,key) {
          var quantity = $('.product_quantity'+key);
          if(quantity.val() > 100){
            alert('超過購物車設限');
            return;
          }
        $.ajax({
           url: '{{ checkout_update }}',
           type: 'post', 
           data: 'product_id=' + product_id + '&quantity=' + quantity.val(),
           dataType: 'json',

           success: function(json) {
            if (json['error']) {
              quantity.val(json['quantity']);
              alert(json['error']);  
            } 
            $('.i-cart-txt').html(json['total']+'件商品');
            $('.cart-list').load('{{ cart_info }} .cart-list-box');
            $('.reload-price').load('{{ checkcart }} ._reload-price');
           }
        });
      },
       //<!-- 訂貨數量走庫存 -->
      'diminish':function(key=0){
        var quantity = $('.product_quantity'+key);
        if(quantity.val() > 1){
          $num = parseInt(quantity.val());
          $num--;
          quantity.val($num);
        }
      },
      'plus':function(product_id,is_plus=true,key=0){
        var quantity = $('.product_quantity'+key);
        $.ajax({
           url: '{{ checkout_plus }}',
           type: 'post', 
           data: 'product_id=' + product_id + '&quantity=' + quantity.val() + '&is_plus=' + is_plus,
           dataType: 'json',

           success: function(json) {
             if(json['error'])
             { 
              quantity.val(json['quantity']);
              alert(json['error']);
             } else if(is_plus==true) {
              $num = parseInt(quantity.val());
              $num++;
              quantity.val($num);
             }
           }
        });
      },
    }