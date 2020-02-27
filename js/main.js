fetch('https://gtlng.github.io/mvheustreu/js/htmlcomponents/footer.html').then(function (response) {

    // The API call was successful!
    return response.text();
}).then(function (html) {

    // This is the HTML from our response as a text string
    // console.log(html);
    document.getElementById("mv-footer").innerHTML = html;
    //newHTML = html;
}).catch(function (err) {
    // There was an error
    console.warn('Something went wrong.', err);
});


fetch('https://gtlng.github.io/mvheustreu/js/htmlcomponents/header.html').then(function (response) {

    // The API call was successful!
    return response.text();
}).then(function (html) {

    // This is the HTML from our response as a text string

    document.getElementById("header").innerHTML = html;
    //newHTML = html;
    (function (window, document) {
        document.getElementById('toggle').addEventListener('click', function (e) {
            document.getElementById('tuckedMenu').classList.toggle('custom-menu-tucked');
            document.getElementById('toggle').classList.toggle('x');
        });
    })(this, this.document);
    var headID = document.head.id;
    console.log(headID);
    document.getElementById(headID).classList.add('pure-menu-selected');
}).catch(function (err) {
    // There was an error
    console.warn('Something went wrong.', err);
});