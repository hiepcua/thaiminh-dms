$(document).ready(function () {
    const formatAmount = (_amount) => {
        return new Intl.NumberFormat('vi-VN').format(_amount);
    }

    const caculateProductDetail = (detailProducts) => {
        let countProduct = 0;
        let totalAmountOrder = 0;
        let totalQtyOrder = 0;
        let listProductOrder = $("#list-product-order");
        listProductOrder.html(``);

        if (detailProducts.length) {
            $('.row-total').removeClass('d-none');
        } else {
            $('.row-total').addClass('d-none');
        }

        detailProducts.sort(function(a, b){
            if(a.name < b.name) { return -1; }
            if(a.name > b.name) { return 1; }

            return 0;
        })

        detailProducts.map(function (product, key) {
            countProduct++;
            totalAmountOrder += product.totalAmount;
            totalQtyOrder += parseInt(product.qty);
            listProductOrder.append(`
                <tr>
                    <td>${countProduct}</td>
                    <td>${product.name}</td>
                    <td class="text-end">${parseInt(product.qty)}</td>
                    <td class="text-end">${formatAmount(product.price)}</td>
                    <td class="text-end">${formatAmount(product.totalAmount)}</td>
                </tr>
            `)
        })
        $('.row-total-amount').html(formatAmount(totalAmountOrder));
        $('.row-total-qty').html(formatAmount(totalQtyOrder));
    }

    const reRenderProductList = () => {
        OLD_PRODUCTS = [];
        $('.input-product-qty').map(function (key, input) {
            let currentInput = $(input).first();
            if (currentInput.val() != 0) {
                OLD_PRODUCTS[currentInput.attr('data-product-id')] = {
                    name: currentInput.attr('data-name'),
                    qty: currentInput.val(),
                    price: currentInput.attr('data-price'),
                    totalAmount: currentInput.val() * currentInput.attr('data-price'),
                }
            }
        });

        caculateProductDetail(OLD_PRODUCTS);
    }

    const handleShowProducts = (products) => {
        let newProductList = '';
        Object.keys(products).map(function (group) {
            newProductList += `<div class="col-md-4">
                                <b class="">${group}</b>
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th style="width: 92px">Số lượng</th>
                                    </tr>
                                    </thead>
                                    <tbody>`;
            Object.keys(products[group]).map(function (productId) {
                if (products[group][productId]['status'] != 0) {
                    newProductList += `<tr>
                                        <td>${products[group][productId]['name']}</td>
                                        <td>
                                            <input class="form-control w-100 input-product-qty"
                                                ${DISABLE_INPUT ? 'disabled' : ''}
                                                type="number"
                                                data-name="${products[group][productId]['name']}"
                                                data-price="${products[group][productId]['price']}"
                                                data-product-id="${productId}"
                                                name="products[${productId}]"
                                                value="${OLD_PRODUCTS[productId] ? (OLD_PRODUCTS[productId]['qty'] ?? 0) : 0}"
                                            >
                                        </td>
                                    </tr>`;
                }
            })
            newProductList += `</tbody>
                                </table>
                            </div>`;
        })

        $("#input-products").html(newProductList);

        reRenderProductList();
    }

    $('#booking_at').on('change', function () {
        let bookingAt = $(this).val();
        ajax(ROUTE_GET_PRODUCT_GROUPED, 'POST', {
            bookingAt: {
                from: bookingAt,
                to: bookingAt,
            }
        })
            .done(async (response) => {
                handleShowProducts(response.products);
            }).fail((error) => {
            console.log(error);
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: "Đã có lỗi xảy ra trên server. Vui lòng thử lại sau.",
                showConfirmButton: false,
                timer: 1500
            })
        });
    })

    $('#booking_at').trigger('change')

    caculateProductDetail(OLD_PRODUCTS);

    $(document).on('change', '.input-product-qty', function () {
        reRenderProductList();
    });


    $('#locality_id').on('change', function () {
        let locality = $(this).val();
        ajax(ROUTE_GET_AGENCY, 'POST', {
            locality
        })
            .done(async (response) => {
                if (response.result) {
                    let select = $("#agency_id");
                    select.html("");
                    select.append(`<option value="">- Lựa chọn -</option>`);
                    Object.keys(response.agencies).map(function (key) {
                        let text = response.agencies[key] ?? '';
                        select.append(`<option value="${key}">${text}</option>`);
                    })
                } else {
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: "Đã có lỗi xảy ra trên server. Vui lòng thử lại sau.",
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
            }).fail((error) => {
            console.log(error);
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: "Đã có lỗi xảy ra trên server. Vui lòng thử lại sau.",
                showConfirmButton: false,
                timer: 1500
            })
        });
    })

    $(document).on("click", ".btn-add-agency-order", function () {
        event.preventDefault();
        $("#form-create-agency-order").submit();
    });
})
