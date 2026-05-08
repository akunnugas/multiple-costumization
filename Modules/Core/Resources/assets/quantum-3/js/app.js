import * as bootstrap from 'bootstrap';
import * as quantum from '@quantum/web/src/index.esm.js';
import * as List from "./app-list";
import * as Form from "./app-form";
import './jquery-init';

window.bootstrap = bootstrap;

window.List = List;
window.Form = Form;

// initialize
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
    new bootstrap.Tooltip(el);
});
document.querySelectorAll('[data-bs-toggle="popover"]').forEach((el) => {
    new bootstrap.Popover(el);
});