function deleteProduct(event) {
    event.preventDefault();
	let button = event.target;
    let product = button.closest("#product");
    let xhr = new XMLHttpRequest();
    let URL = Routing.generate('deleteProduct', {"id": button.dataset.productId});
    xhr.open("DELETE", URL, true);
    xhr.onload = function () {
        product.remove();
    };
    xhr.send(null);
}

let buttons = document.getElementsByClassName("delete-product");
Array.from(buttons).forEach(function (button) {
    button.addEventListener("click", deleteProduct);
});

window.setTimeout("document.getElementById('successMessage').style.display='none';", 2000);

window.setTimeout(() => {
    const elements = document.getElementsByClassName('flashes');
    Array.from(elements).forEach((element) => {
        element.style.display = 'none';
    });
}, 5000);
