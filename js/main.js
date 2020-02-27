class MyComponent extends HTMLElement {
    connectedCallback() {
        this.innerHTML = '<h1>Hello world</h1>';
    }
}

customElements.define('my-component', MyComponent);



class Footer extends HTMLElement {
    connectedCallback() {
        this.innerHTML = `<div class="footer l-box is-center">
      View the source of this layout to learn more. Made with love by the YUI Team.
  </div>`;
    }
}

customElements.define('mv-footer', Footer);

fetch('htmlcomponents/footer.html')
    .then((response) => {
        return response.json();
    })
    .then((data) => {
        console.log(data);
    });


fetch('js/htmlcomponents/footer.html').then(function (response) {
   
    // The API call was successful!
    return response.text();
}).then(function (html) {
    
    // This is the HTML from our response as a text string
    console.log(html);
}).catch(function (err) {
    // There was an error
    console.warn('Something went wrong.', err);
});


class Footer2 extends HTMLElement {
    connectedCallback() {
        this.innerHTML = `<div class="footer l-box is-center">
      View the source of this layout to learn more. Made with love by the YUI Team.
  </div>`;
    }
}

customElements.define('mv-footer2', Footer);

