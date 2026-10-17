/* Runs before CSS so saved light mode does not flash dark on page navigation. */
(() => {
  let theme = "dark";
  try {
    if (localStorage.getItem("sportshub-theme") === "light") theme = "light";
  } catch {}
  document.documentElement.dataset.theme = theme;
})();
