/**
 * This script will injected to wordpress site
 * @version 0.0.1
 * @author The resadev team
 */

import { Switch } from "../switch/src/switch";
import "../css/style.css";

/**
 *
 * @param {Element} element
 * @param {string} className
 */
const delClass = (element, className) => element.classList.remove(className);

/**
 * @function
 * @name changeState
 * @returns void changes state
 */
const changeState = (swi) => swi.autoChangeTheme();

/**
 * @function
 * @name btn
 */
const btn = (swit) =>
    document
        .getElementById("switch-btn")
        .addEventListener("click", () => changeState(swit));

/**
 * @param {MouseEvent} event
 */
const closeNav = (obj) => obj.map((item) => delClass(item.element, item.class));

/**
 * This function runst when script injected
 */
const main = () => {
    const element = document.querySelector("body");

    if(Switch.switchExist()){
        let swit = new Switch({ element: element });
        swit.changeTheme(localStorage.getItem("theme"));
        btn(swit);
    }

    /**
     * @name bodyClick
     * @param {MouseEvent} event
     */
    const bodyClick = (event) => {
        const navClasses = [
            {
                element: document.querySelector("nav .has-modal-open"),
                class: "is-menu-open",
            },
            {
                element: document.querySelector("html"),
                class: "has-modal-open",
            },
        ];
        switch (event.target) {
            case document.body:
                closeNav(navClasses);
                break;
                
            /// Any Link click ?
            case document.querySelector('nav .has-modal-open span'):
                closeNav(navClasses);
                break;

            default:
                break;
        }
    };

    window.addEventListener("click", bodyClick);
};

window.addEventListener("DOMContentLoaded", main);
