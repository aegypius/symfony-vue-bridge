import { createApp, defineCustomElement } from "vue";
import HelloWorld from "./components/MyInput.vue";
import MyForm from "./components/MyForm.vue";
import MyInput from "./components/MyInput.vue";

// createApp(HelloWorld).mount("#app");

customElements.define('my-form', defineCustomElement(MyForm));
customElements.define('my-input', defineCustomElement(MyInput));
