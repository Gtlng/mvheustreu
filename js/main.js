document.addEventListener("DOMContentLoaded", function (event) {
    // Holt den Header
    fetch('https://gtlng.github.io/mvheustreu/js/htmlcomponents/header.html').then(function (response) {
        return response.text();
    }).then(function (html) {
        document.getElementById("header").innerHTML = html;
        // Funktion, um das Menüband in der mobilen Ansicht ausklappen zu können
        (function (window, document) {
            document.getElementById('toggle').addEventListener('click', function (e) {
                document.getElementById('tuckedMenu').classList.toggle('custom-menu-tucked');
                document.getElementById('toggle').classList.toggle('x');
            });
        })(this, this.document);
        // Funktion, um die aktuell aufgerufene Seite im Menüband hervorzuheben; setzt eine id im head und in der Liste der Navigationselemente voraus
        var headID = document.head.id;
        document.getElementById(headID + "_nav").className += " pure-menu-selected";

    }).catch(function (err) {
        // There was an error
        console.warn('Something went wrong.', err);
    });


    // Holte den Footer
    fetch('https://gtlng.github.io/mvheustreu/js/htmlcomponents/footer.html').then(function (response) {
        // The API call was successful!
        return response.text();
    }).then(function (html) {

        // This is the HTML from our response as a text string
        // Füllt den Footer mit dem abgerufenen HTML-Text
        document.getElementById("mv-footer").innerHTML = html;
        //newHTML = html;
    }).catch(function (err) {
        // There was an error
        console.warn('Something went wrong.', err);
    });
});