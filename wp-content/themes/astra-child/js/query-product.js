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
        console.log('Product updated in cookies:', productList[existingProductIndex]);
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
            console.log('Product list trimmed to 10 items');
        }

        console.log('New product added to cookies:', newProduct);
    }

    // 存储到cookies（有效期7天）
    const expiryDate = new Date();
    expiryDate.setDate(expiryDate.getDate() + 7);
    document.cookie = `inquiry_products=${encodeURIComponent(JSON.stringify(productList))}; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;

    console.log('Total products in cookies:', productList.length);
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

        console.log('Product removed from cookies:', productId);
        return true;
    }

    return false;
}

// 清除所有产品cookies
function clearProductCookies() {
    document.cookie = 'inquiry_products=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    console.log('All products cleared from cookies');
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

// 更新产品列表显示（用于联系表单右侧）
// function updateProductListInContactForm() {
//     const productList = getProductListFromCookies();
//     const productInfoContainer = jQuery('#query-form-product-list');

//     if (productInfoContainer.length) {
//         jQuery("#query-form-fields").addClass('has-products');
//         jQuery("#query-form-product-list").addClass('has-products');

//         if (productList.length > 0) {
//             let productsHTML = `
//                 <div class="inquiry-products-list">
//                     <h3>Inquiry Products (${productList.length})</h3>
//                     <div class="products-container">
//             `;

//             productList.forEach((product, index) => {
//                 productsHTML += `
//                     <div class="inquiry-product-item" data-product-id="${product.id}">
//                         <div class="product-image">
//                             <img src="${product.image}" alt="${product.title}" style="max-width: 80px; height: auto;">
//                         </div>
//                         <div class="product-info">
//                             <h4>${product.title}</h4>
//                             <p><a href="${product.url}" target="_blank">View Product</a></p>
//                             <button class="remove-product-btn" data-product-id="${product.id}">× Remove</button>
//                         </div>
//                         <input type="hidden" name="inquiry_products[${index}][id]" value="${product.id}">
//                         <input type="hidden" name="inquiry_products[${index}][title]" value="${product.title}">
//                         <input type="hidden" name="inquiry_products[${index}][url]" value="${product.url}">
//                         <input type="hidden" name="inquiry_products[${index}][image]" value="${product.image}">
//                     </div>
//                 `;
//             });

//             productsHTML += `
//                     </div>
//                 </div>
//             `;

//             productInfoContainer.html(productsHTML);

//             // 绑定移除按钮事件
//             jQuery('.remove-product-btn').on('click', function () {
//                 const productId = jQuery(this).data('product-id');
//                 removeProductFromCookies(productId);
//                 updateProductListInContactForm(); // 刷新显示
//             });

//         } else {
//             productInfoContainer.html(`
//                 <div class="no-products-message">
//                     <p>No products added to inquiry list yet.</p>
//                 </div>
//             `);
//         }
//     } else {
//         jQuery("#query-form-fields").removeClass('has-products');
//         jQuery("#query-form-product-list").removeClass('has-products');
//     }
// }




function updateProductListInContactForm() {
    const productList = getProductListFromCookies();
    const productInfoContainer = jQuery('#query-form-product-list');
    const hiddenTextarea = jQuery('#form-field-query_product');

    if (productInfoContainer.length && productList.length > 0) {

        jQuery("#query-form-fields").addClass('has-products');
        jQuery("#query-form-product-list").addClass('has-products');


        // 构建JSON格式数据
        // const productData = {
        //     count: productList.length,
        //     products: productList.map(product => ({
        //         id: product.id,
        //         title: product.title,
        //         url: product.url,
        //         image: product.image,
        //         added_at: product.timestamp
        //     }))
        // };

        // 更新隐藏字段（JSON格式）
        // if (hiddenTextarea.length) {
        //     hiddenTextarea.val(JSON.stringify(productData, null, 2));
        // }

        if (productList.length > 0) {
            // 生成易读的产品列表


            console.log("Generating readable product list for hidden textarea");

            let readableText = `=== INQUIRY PRODUCTS (${productList.length}) ===\n\n`;

            productList.forEach((product, index) => {
                readableText += `PRODUCT ${index + 1}:\n`;
                readableText += `Title: ${product.title}\n`;
                readableText += `URL: ${product.url}\n`;
                readableText += `ID: ${product.id}\n`;
                readableText += `Added: ${new Date(product.timestamp).toLocaleString()}\n`;
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











// 显示成功弹窗
function showSuccessPopup() {
    // 使用Elementor的方法打开弹窗
    // if (typeof elementorPro !== 'undefined') {
    //     // 假设你的成功弹窗ID是 'inquiry_success_popup'
    //     elementorPro.modules.popup.showPopup({ id: 'inquiry_success_popup' });
    // } else {
    //     // 备用方法
    //     jQuery('.inquiry-success-popup').fadeIn();
    // }

    // jQuery('#open_query_form').trigger('click');
}

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



    // if ( window.elementorProFrontend && window.elementorProFrontend.modules && window.elementorProFrontend.modules.popup ) {
    //         try {
    //             var popup = window.elementorProFrontend.modules.popup;
    //             if ( typeof popup.closePopup === 'function' ) {
    //                 popup.closePopup();
    //                 return;
    //             }
    //             if ( typeof popup.hidePopup === 'function' ) {
    //                 popup.hidePopup();
    //                 return;
    //             }
    //         } catch(e){}
    //     }
    //     // 兜底事件触发
    //     jQuery(document).trigger('elementor/popup/hide');
}

// 打开联系表单弹窗
function openContactFormPopup() {
    // // 假设你的联系表单弹窗ID是 'contact_form_popup'
    // if (typeof elementorPro !== 'undefined') {
    //     elementorPro.modules.popup.showPopup({ id: 'contact_form_popup' });
    // }

    jQuery('#open_query_form').trigger('click');

    updateProductListInContactForm();

    // 动态更新联系表单右侧的产品信息
    // updateContactFormProductInfo();
}




// // 更新联系表单右侧的产品信息
// function updateContactFormProductInfo() {
//     const productData = getProductFromCookies();

//     console.log('Retrieved product data from cookies:', productData);


//     const productInfoContainer = $('#inquiry-product-info');

//     if (productData && productInfoContainer.length) {
//         const productHTML = `
//             <div class="inquiry-product-details">
//                 <h3>Product Inquiry</h3>
//                 <div class="product-image">
//                     <img src="${productData.image}" alt="${productData.title}" style="max-width: 100%; height: auto;">
//                 </div>
//                 <div class="product-info">
//                     <h4>${productData.title}</h4>
//                     <p><a href="${productData.url}" target="_blank">View Product</a></p>
//                     <input type="hidden" name="inquiry_product_id" value="${productData.id}">
//                     <input type="hidden" name="inquiry_product_title" value="${productData.title}">
//                     <input type="hidden" name="inquiry_product_url" value="${productData.url}">
//                     <input type="hidden" name="inquiry_product_image" value="${productData.image}">
//                 </div>
//             </div>
//         `;
//         productInfoContainer.html(productHTML);
//     }
// }

// 当联系表单弹窗打开时自动更新产品信息
// jQuery(document).on('elementor/popup/show', function(event, id, instance) {
//     if (id === 'contact_form_popup') { // 替换为你的联系表单弹窗ID
//         setTimeout(updateContactFormProductInfo, 500);
//     }


//     console.log('Popup shown with ID:', id);
// });




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

        // 显示成功弹窗
        // showSuccessPopup();

        const pl = getProductListFromCookies();
        console.log('Current products in cookies:', pl);
    });

    // 当联系表单打开时更新产品列表
    // jQuery(document).on('elementor/popup/show', function (event, id, instance) {
    //     if (id === 'contact_form_popup') { // 替换为你的联系表单弹窗ID
    //         setTimeout(updateProductListInContactForm, 500);
    //     }
    // });
});





jQuery(document).ready(function ($) {
    let formSubmitted = false;



    $(document).on('submit_success', '.elementor-form', function (e, response) {
        // closeSuccessPopup();
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


    // // 监听表单提交成功
    // $(document).on('elementor_pro/forms/success', function (event, response) {
    //     console.log('Form submitted successfully', response);
    //     formSubmitted = true;

    //     // 清除产品cookies
    //     clearProductCookies();
    //     console.log('Product cookies cleared after form submission');

    //     // 延迟关闭弹窗，让用户看到成功状态
    //     setTimeout(closeSuccessPopup, 2000);
    //     // setTimeout(closeFormPopup, 2000);
    // });

    // // 监听弹窗关闭事件，确保cookies被清除
    // $(document).on('elementor/popup/hide', function (event, id, instance) {
    //     if (formSubmitted) {
    //         console.log('Popup closed after successful form submission');
    //         formSubmitted = false; // 重置状态
    //     }
    // });

    // // 关闭表单弹窗的函数
    // function closeFormPopup() {
    //     const activeDialog = elementorFrontend?.utils?.dialogsManager?.getActiveDialog();
    //     if (activeDialog) {
    //         console.log('Closing form popup via Dialogs Manager');
    //         activeDialog.hide();
    //     } else {
    //         console.log('Closing form popup via DOM manipulation');
    //         $('.elementor-popup-modal').fadeOut(300, function() {
    //             $(this).remove();
    //         });
    //         $('body').removeClass('dialog-prevent-scroll').css('overflow', '');
    //     }
    // }


});