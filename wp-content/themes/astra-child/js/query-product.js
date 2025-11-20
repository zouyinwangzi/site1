/**
 * 流程说明：
 * 1. 用户点击“Reserve Now”按钮（#reservation-now-btn），产品信息被存储到cookies中。
 * 2. 同时，自带的链接触发elementor监控的popup按钮点击事件，显示成功弹窗，用户可以选择继续浏览或立即询价。
 * 3. 如果用户选择“立即询价”（#inquiry-now-btn），关闭成功弹窗并打开联系表单弹窗（触发#open_query_form的点击，它自身也带elementor的popup链接），同时更新联系表单中的产品列表。
 * 4. 联系表单弹窗打开时，右侧显示已添加的产品列表，用户可以移除不需要的产品。
 * 5. 用户填写并提交联系表单后，表单提交成功事件触发，等待2秒后关闭弹窗，并清除cookies中的产品信息。
 * 6. 如果用户关闭联系表单弹窗，产品信息仍保留在cookies中，方便下次打开时继续使用。
 * 7. 整个过程中，产品信息通过cookies进行存储和管理，确保用户体验流畅且数据持久。
 * 8. 侧边栏的按钮（#open_query_form），也绑定了更新产品列表的功能，确保每次打开联系表单时，产品信息都是最新的。
 * 9. 要生成elementor的popup链接，参考php代码：    printf(
        '<a href="#%s" id="reservation-now-btn" class="reservation-now-btn button" %s>Reservation Now</a>',
        urlencode(sprintf('elementor-action:action=popup:open&settings=%s', base64_encode('{"id":"667","toggle":false}'))),
        $data_attributes
    );
 * 
 */



// 存储产品信息到cookies（支持多个产品）
function storeProductInCookies(productId, productTitle, productUrl, productImage) {
    // 获取现有的产品列表
    let productList = getProductListFromCookies();

    // 检查产品是否已存在
    const existingProductIndex = productList.findIndex(product => product.id === productId);

    if (existingProductIndex !== -1) {
        // 如果产品已存在，更新时间和信息（可选）
        productList[existingProductIndex] = {
            ...productList[existingProductIndex],
            title: productTitle,
            url: productUrl,
            image: productImage,
            timestamp: new Date().getTime()
        };
        // console.log('Product updated in cookies:', productList[existingProductIndex]);
    } else {
        // 添加新产品到列表开头
        const newProduct = {
            id: productId,
            title: productTitle,
            url: productUrl,
            image: productImage,
            timestamp: new Date().getTime()
        };
        productList.unshift(newProduct); // 添加到开头，最新的在前面

        // 限制列表长度（可选，比如最多保存10个产品）
        if (productList.length > 10) {
            productList = productList.slice(0, 10);
            // console.log('Product list trimmed to 10 items');
        }

        // console.log('New product added to cookies:', newProduct);
    }

    // 存储到cookies（有效期7天）
    const expiryDate = new Date();
    expiryDate.setDate(expiryDate.getDate() + 7);
    document.cookie = `inquiry_products=${encodeURIComponent(JSON.stringify(productList))}; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;

    // console.log('Total products in cookies:', productList.length);
    return productList;
}

// 从cookies获取产品列表
function getProductListFromCookies() {
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'inquiry_products' && value) {
            try {
                return JSON.parse(decodeURIComponent(value)) || [];
            } catch (e) {
                console.error('Error parsing product list from cookies:', e);
            }
        }
    }
    return [];
}

// 获取单个产品信息（向后兼容）
function getProductFromCookies(productId = null) {
    const productList = getProductListFromCookies();

    if (productId) {
        // 返回指定ID的产品
        return productList.find(product => product.id === productId) || null;
    } else {
        // 返回最新的产品（向后兼容）
        return productList.length > 0 ? productList[0] : null;
    }
}

// 从列表中移除特定产品
function removeProductFromCookies(productId) {
    let productList = getProductListFromCookies();
    const initialLength = productList.length;

    productList = productList.filter(product => product.id !== productId);

    if (productList.length !== initialLength) {
        // 更新cookies
        const expiryDate = new Date();
        expiryDate.setDate(expiryDate.getDate() + 7);
        document.cookie = `inquiry_products=${encodeURIComponent(JSON.stringify(productList))}; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;

        // console.log('Product removed from cookies:', productId);
        return true;
    }

    return false;
}

// 清除所有产品cookies
function clearProductCookies() {
    document.cookie = 'inquiry_products=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    // console.log('All products cleared from cookies');
}

// 获取产品列表数量
function getProductCountFromCookies() {
    return getProductListFromCookies().length;
}

// 检查产品是否已在列表中
function isProductInCookies(productId) {
    const productList = getProductListFromCookies();
    return productList.some(product => product.id === productId);
}





function updateProductListInContactForm() {
    const productList = getProductListFromCookies();
    const productInfoContainer = jQuery('#query-form-product-list');
    const hiddenTextarea = jQuery('#form-field-query_product');

    if (productInfoContainer.length && productList.length > 0) {

        jQuery("#query-form-fields").addClass('has-products');
        jQuery("#query-form-product-list").addClass('has-products');

        if (productList.length > 0) {
            // 生成易读的产品列表


            // console.log("Generating readable product list for hidden textarea");

            let readableText = `=== INQUIRY PRODUCTS (${productList.length}) ===\n\n`;

            productList.forEach((product, index) => {
                readableText += `PRODUCT ${index + 1}:\n`;
                readableText += `Title: ${product.title}\n`;
                readableText += `URL: ${product.url}\n`;
                readableText += `ID: ${product.id}\n`;
                readableText += `----------------------------\n\n`;
            });

            hiddenTextarea.val(readableText);
        } else {
            hiddenTextarea.val('No products in inquiry list');
        }

        // 构建显示HTML（保持不变）
        let productsHTML = `
            <div class="inquiry-products-list">
                <h3>Inquiry Products (${productList.length})</h3>
                <div class="products-container">
        `;

        productList.forEach((product) => {
            productsHTML += `
                <div class="inquiry-product-item" data-product-id="${product.id}">
                    <div class="product-image">
                        <img src="${product.image}" alt="${product.title}" style="max-width: 80px; height: auto;">
                    </div>
                    <div class="product-info">
                        <p class="product-title"><a href="${product.url}" target="_blank">${product.title}</a></p>
                        <div class="remove-product"><a href="javascript:return void();" class="remove-product-btn" data-product-id="${product.id}">× Remove</a></div>
                    </div>
                </div>
            `;
        });

        productsHTML += `</div></div>`;
        productInfoContainer.html(productsHTML);

        // 绑定移除事件
        jQuery('.remove-product-btn').on('click', function () {
            const productId = jQuery(this).data('product-id');
            removeProductFromCookies(productId);
            updateProductListInContactForm();
        });

    } else {
        // 清空状态
        productInfoContainer.html('<div class="no-products-message"><p>No products added to inquiry list yet.</p></div>');
        if (hiddenTextarea.length) {
            hiddenTextarea.val('');
        }


        jQuery("#query-form-fields").removeClass('has-products');
        jQuery("#query-form-product-list").removeClass('has-products');
    }
}



// 打开联系表单弹窗
function openContactFormPopup() {
    jQuery('#open_query_form').trigger('click');

    // updateProductListInContactForm();

}


// 显示成功弹窗
function showSuccessPopup() {

}

// 关闭成功弹窗
function closeSuccessPopup() {
    // 阻止默认行为
    if (event !== undefined) {
        event.preventDefault();
        event.stopPropagation();
    }

    const activeDialog = elementorFrontend?.utils?.dialogsManager?.getActiveDialog();

    if (activeDialog) {
        // 在隐藏前保存滚动位置
        const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

        activeDialog.hide();

        // 立即恢复滚动位置
        requestAnimationFrame(() => {
            window.scrollTo(0, scrollPosition);
        });
    } else {
        jQuery('.elementor-popup-modal').hide();
        jQuery('body').removeClass('dialog-prevent-scroll');
    }

}









jQuery(document).ready(function ($) {
    jQuery(document).on('click', '#reservation-now-btn', function (event) {
        event.preventDefault();

        // 从数据属性获取产品信息
        const productId = jQuery(this).data('product-id');
        const productTitle = jQuery(this).data('product-title');
        const productUrl = jQuery(this).data('product-url');
        const productImage = jQuery(this).data('product-image');

        // 存储产品信息到cookies（支持多个）
        storeProductInCookies(productId, productTitle, productUrl, productImage);

    });

});





// 绑定成功弹窗按钮事件
jQuery(document).ready(function ($) {
    // Continue按钮 - 关闭弹窗
    jQuery(document).on('click', '#inquiry-continue-btn', function () {
        closeSuccessPopup();
    });

    // Inquire Now按钮 - 打开联系表单
    jQuery(document).on('click', '#inquiry-now-btn', function () {
        closeSuccessPopup();
        setTimeout(function () {
            openContactFormPopup();
        }, 500);
    });
});




jQuery(document).ready(function ($) {
    jQuery(document).on('click', function (event) {
        updateProductListInContactForm();

    });
});








/**表单发送按钮点击后，展示出提交成功信息后，等待2s关闭整个弹窗 */
jQuery(document).ready(function ($) {
    let formSubmitted = false;

    $(document).on('submit_success', '.elementor-form', function (e, response) {
        setTimeout(closeSuccessPopup, 2000);
        clearProductCookies();

    });


    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            (m.addedNodes || []).forEach(function (node) {
                if (!(node instanceof HTMLElement)) return;
                if (node.matches && node.matches('.elementor-message.elementor-message-success')) {
                    // closeSuccessPopup();
                    setTimeout(closeSuccessPopup, 2000);

                    clearProductCookies();

                } else if (node.querySelector && node.querySelector('.elementor-message.elementor-message-success')) {
                    // closeSuccessPopup();
                    setTimeout(closeSuccessPopup, 2000);

                    clearProductCookies();

                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });


});