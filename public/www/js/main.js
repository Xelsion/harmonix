document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("textarea.auto-resize").forEach((textarea) => {

        const resize = () => {
            textarea.style.height = "auto"; // Reset height to calculate correctly

            // Get computed padding to add it to the scrollHeight
            const computed = window.getComputedStyle(textarea);
            const paddingTop = parseFloat(computed.paddingTop) || 0;
            const paddingBottom = parseFloat(computed.paddingBottom) || 0;
            const borderTop = parseFloat(computed.borderTopWidth) || 0;
            const borderBottom = parseFloat(computed.borderBottomWidth) || 0;

            // Calculate exact outer height needed
            const exactHeight = textarea.scrollHeight + borderTop + borderBottom;

            textarea.style.height = exactHeight + "px";
        };

        // 1. Adjust height initially on page load
        resize();

        // 2. Adjust height dynamically while typing
        textarea.addEventListener("input", resize);
    });
});
