import './bootstrap';
import { initProjectShowMap, initProjectFormMap, initProjectsMap } from './maps.js';

// Make map functions globally available
window.initProjectShowMap = initProjectShowMap;
window.initProjectFormMap = initProjectFormMap;
window.initProjectsMap = initProjectsMap;

function fieldMatchesLaravelRules(field, form) {
	const value = field.value.trim();
	const rules = (field.dataset.validationRules || '').split('|').filter(Boolean);

	return rules.every(rule => {
		const [name, parameter] = rule.split(':', 2);

		if (name === 'required') {
			return field.type === 'file' ? field.files.length > 0 : value !== '';
		}

		if (name === 'max') {
			return field.type === 'file'
				? !field.files.length || field.files[0].size <= Number(parameter) * 1024
				: value.length <= Number(parameter);
		}

		if (name === 'date') {
			return value === '' || !Number.isNaN(Date.parse(value));
		}

		if (name === 'before-now') {
			return value === '' || Date.parse(value) <= Date.now();
		}

		if (name === 'after-or-equal') {
			const otherField = form.elements.namedItem(parameter);
			return value !== '' && otherField && Date.parse(value) >= Date.parse(otherField.value);
		}

		if (name === 'in') {
			return parameter.split(',').includes(value);
		}

		if (name === 'mimes') {
			if (!field.files.length) return true;
			const extension = field.files[0].name.split('.').pop().toLowerCase();
			return parameter.split(',').includes(extension);
		}

		if (name === 'exists') {
			return value === '' || Array.from(field.options).some(option => option.value === value);
		}

		return true;
	});
}

function refreshLaravelFieldErrors(form) {
	form.querySelectorAll('[data-laravel-error-for]').forEach(error => {
		const field = form.elements.namedItem(error.dataset.laravelErrorFor);
		if (!field || typeof field.value !== 'string') return;

		const valid = fieldMatchesLaravelRules(field, form);
		error.hidden = valid;
		field.setAttribute('aria-invalid', valid ? 'false' : 'true');
	});
}

document.addEventListener('input', event => {
	const form = event.target.closest('form');
	if (form) refreshLaravelFieldErrors(form);
});

document.addEventListener('change', event => {
	const form = event.target.closest('form');
	if (form) refreshLaravelFieldErrors(form);
});
