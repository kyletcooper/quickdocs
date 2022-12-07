(function () {
	'use strict';

	const helpfulBtn = document.querySelector("[data-helpful]");
	const unhelpfulBtn = document.querySelector("[data-unhelpful]");
	const userStateKey = "quickdocs-helpful";
	const userState = JSON.parse(localStorage.getItem(userStateKey) || "{}");

	const saveState = () => {
		localStorage.setItem(userStateKey, JSON.stringify(userState));

		helpfulBtn.classList.remove("active");
		unhelpfulBtn.classList.remove("active");

		if (isHelpful(quickdocs.post_id)) {
			helpfulBtn.classList.add("active");
		}
		else if (isUnhelpful(quickdocs.post_id)) {
			unhelpfulBtn.classList.add("active");
		}
	}

	const sendHelpfullness = (id, helpfulOrUnhelpful) => {
		fetch(`${quickdocs.rest_url}quickdocs/v1/${helpfulOrUnhelpful}/${quickdocs.post_id}`, {
			method: 'POST',
			headers: new Headers({
				'X-WP-Nonce': quickdocs.rest_nonce
			})
		});
	}

	const markAsHelpful = id => {
		if (!isHelpful(id)) {
			userState[id] = "helpful";
			sendHelpfullness(id, "helpful");
			saveState();
		}
	}

	const markAsUnhelpful = id => {
		if (!isUnhelpful(id)) {
			userState[id] = "unhelpful";
			sendHelpfullness(id, "unhelpful");
			saveState();
		}
	}

	const isHelpful = id => {
		return userState[id] == "helpful";
	}

	const isUnhelpful = id => {
		return userState[id] == "unhelpful";
	}

	helpfulBtn.addEventListener("click", () => markAsHelpful(quickdocs.post_id));
	unhelpfulBtn.addEventListener("click", () => markAsUnhelpful(quickdocs.post_id));
	saveState();

})();