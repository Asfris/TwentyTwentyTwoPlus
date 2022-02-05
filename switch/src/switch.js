'use strict';

/**
 * Switch module for handle Switch Themes
 * @version 0.0.1
 * @author The resadev team
 */

const themes = { DARK: "dark", LIGHT: "light", SYSTEM: "system" };

/**
 * @name storageCheck
 * @description Reads localStorage and check if option is exists
 * @param {any} option
 * @param {any} value
 * @returns void
 */
const storageCheck = (option, value) =>
	window.localStorage.getItem(option) === null ?
		window.localStorage.setItem(option, value) : null;


/**
 * @name Switch
 * @description Handles Themes
 */
export class Switch {
	/**
	 * @name state
	 * @description State of theme
	 */
	state = themes.SYSTEM;

	/**
	 * @name element
	 * @description Element for apply theme
	 * @type Element
	 */
	element;

	/**
	 * @class
	 * @param {Element} element for apply theme
	 * @returns Switch
	 */
	constructor({ element }) {
		// When class created
		this.element = element;

		storageCheck("theme", themes.SYSTEM);

		element.classList.add(window.localStorage.getItem("theme"))
	}

	/**
	 * @name formStorage
	 * @description Creates a new Switch object from storage>theme
	 * @param {Element} element for apply theme
	 * @returns Switch
	 */
	static formStorage({ element }) {
		// Get theme from storage
		let result = new Switch({ element });

		storageCheck("theme", themes.SYSTEM);

		result.state = localStorage.getItem('theme');

		return result;
	}

	set state(theme) {
		this.state = theme;
	}

	/**
	 * @name autoChangeState
	 * @description Change state of theme auto
	 */
	autoChangeTheme() {
		const { LIGHT, DARK, SYSTEM } = themes;

		switch (this.state) {
			case DARK:
				this.changeTheme(LIGHT);
				break;

			case LIGHT:
				this.changeTheme(SYSTEM);
				break;

			case SYSTEM:
				this.changeTheme(DARK);
				break;

			default:
				break;
		}
	}

	/**
	 * @name changeTheme
	 * @param state Next state
	 */
	changeTheme(state) {
		// Select element and replace theme class to next theme
		this.element.classList.replace(this.state, state);
		window.localStorage.setItem('theme', state);
		this.state = state;
	}

	/**
	 * @description Return switch element exist
	 * @returns {boolean}
	 */
	static switchExist() {
		return document.getElementById('switch-btn') !== null;
	}
}
