<script setup>
	import { computed } from "vue";

	import { Link } from "@inertiajs/vue3";

	const props = defineProps({
		primary: Boolean,
		default: Boolean,
		secondary: Boolean,
		info: Boolean,
		warning: Boolean,
		error: Boolean,
		success: Boolean,
		rounded: Boolean,
		link: Boolean,
		url: Boolean,
		external: Boolean,
	});
	const colors = computed(() => {
		if (props.default)
			return "bg-gray-200 border-gray-200 text-gray-800 hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-200/80 dark:bg-gray-500 dark:text-gray-50 dark:hover:bg-gray-400 dark:focus:bg-gray-400 dark:active:bg-gray-400/90";
		if (props.primary)
			return "bg-emerald-500 border-emerald-500 text-white hover:bg-emerald-600 focus:bg-emerald-600 active:bg-emerald-600/90";
		if (props.secondary)
			return "text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100  text-sm dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700";
		if (props.success)
			return "bg-success border-success text-white hover:bg-success-focus focus:bg-success-focus active:bg-success-focus/90";
		if (props.warning)
			return "bg-warning border-warning text-white hover:bg-warning-focus focus:bg-warning-focus active:bg-warning-focus/90";
		if (props.error)
			return "bg-error border-error  text-white hover:bg-error-focus focus:bg-error-focus active:bg-error-focus/90";
		return "bg-gray-800 dark:bg-gray-200  border-transparent  text-white dark:text-gray-800  tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 ";
	});
</script>
<template>
	<Link
		v-if="link"
		:class="[rounded ? 'rounded-full' : '', colors]"
		class="btn border font-semibold disabled:opacity-70 disabled:pointer-events-none transition ease-in-out duration-150">
		<slot />
	</Link>
	<a
		v-else-if="url"
		v-bind="
			external
				? { target: '_blank', rel: 'noopener noreferrer nofollow' }
				: {}
		"
		:class="[rounded ? 'rounded-full' : '', colors]"
		class="btn border font-semibold disabled:opacity-70 disabled:pointer-events-none transition ease-in-out duration-150">
		<slot />
	</a>
	<button
		v-else
		:class="[rounded ? 'rounded-full' : '', colors]"
		class="btn border font-semibold disabled:opacity-70 disabled:pointer-events-none transition ease-in-out duration-150">
		<slot />
	</button>
</template>
