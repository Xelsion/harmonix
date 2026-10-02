document.addEventListener("DOMContentLoaded", () => {
    // 1. Central dictionary for language keywords
    const languageKeywords = {
        php: /\b(function|echo|if|else|return|true|false|foreach|as|public|private|protected|class|try|catch|throw|new|mixed|array|string|int|float|null)\b/g,
        csharp: /\b(public|private|protected|internal|class|struct|interface|void|string|int|var|if|else|return|true|false|try|catch|throw|new|using|namespace|async|await|get|set)\b/g,
        cpp: /\b(int|char|float|double|bool|void|class|struct|public|private|protected|if|else|return|true|false|try|catch|throw|new|delete|namespace|include|using|auto)\b/g,
        java: /\b(public|private|protected|class|interface|void|int|double|boolean|String|if|else|return|true|false|try|catch|throw|new|import|package|final|static)\b/g,
        python: /\b(def|class|if|elif|else|return|True|False|try|except|raise|import|from|as|print|in|is|not|and|or|lambda|None|pass)\b/g,
        js: /\b(const|let|var|function|return|if|else|true|false|try|catch|throw|new|class|import|export|from|async|await|null|undefined|typeof)\b/g
    };

    const languageNames = {
        php: "PHP",
        csharp: "C#",
        cpp: "C++",
        java: "Java",
        python: "Python",
        js: "JavaScript"
    };

    document.querySelectorAll("pre.code-container").forEach((preBlock) => {
        const codeBlock = preBlock.querySelector("code");
        if (!codeBlock) return;

        // Detect language directly from the class name (matches dictionary keys)
        let lang = "none";
        const classList = codeBlock.className.split(" ");
        const foundLang = classList.find(c => languageKeywords.hasOwnProperty(c.toLowerCase()));
        if (foundLang) {
            lang = foundLang.toLowerCase();
        }

        // Create Code lable
        if (lang !== "none") {
            const languageLabel = document.createElement("div");
            languageLabel.className = "language-label";
            languageLabel.textContent = "Language: " + languageNames[lang];
            preBlock.appendChild(languageLabel);
        }


        // Get text content and split into lines
        let lines = codeBlock.textContent.split("\n");

        // Correctly check array elements without causing exceptions
        while (lines.length > 0 && lines[0].trim() === "") {
            lines.shift();
        }
        while (lines.length > 0 && lines[lines.length - 1].trim() === "") {
            lines.pop();
        }

        // Convert tabs to 4 spaces early to standardize length measurements
        lines = lines.map(line => line.replace(/\t/g, "    "));

        // Find the minimum indentation of ANY line that actually contains text
        let minIndent = Infinity;
        lines.forEach(line => {
            if (line.trim() !== "") {
                const match = line.match(/^ */);
                const indentLength = match ? match[0].length : 0;
                if (indentLength < minIndent) {
                    minIndent = indentLength;
                }
            }
        });

        // Strip exactly that minimum common indentation from all lines
        if (minIndent > 0 && minIndent !== Infinity) {
            lines = lines.map(line => line.substring(minIndent));
        }

        const highlightedLines = (codeBlock.getAttribute('data-highlight') || '').split(',');

        // Build the HTML and apply safe syntax highlighting
        codeBlock.innerHTML = lines.map((line, index) => {
            const lineNumber = (index + 1).toString();
            const isHighlighted = highlightedLines.includes(lineNumber) ? ' highlighted' : '';

            let escapedLine = line
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;");

            if (lang !== "none") {
                let isFullComment = false;
                const isCommentLine = escapedLine.trim().startsWith('//') || (lang === "python" && escapedLine.trim().startsWith('#'));

                if (isCommentLine) {
                    escapedLine = `<span class="token-comment">${escapedLine}</span>`;
                    isFullComment = true;
                }

                if (!isFullComment) {
                    let commentPart = "";
                    const inlineCommentIndex = lang === "python" ? escapedLine.indexOf('#') : escapedLine.indexOf('//');

                    if (inlineCommentIndex !== -1) {
                        commentPart = `<span class="token-comment">${escapedLine.substring(inlineCommentIndex)}</span>`;
                        escapedLine = escapedLine.substring(0, inlineCommentIndex);
                    }

                    // Protect Strings using placeholders
                    const strings = [];
                    escapedLine = escapedLine.replace(/(["'])(.*?)\1/g, (match) => {
                        strings.push(`<span class="token-string">${match}</span>`);
                        return `___STR_PLACEHOLDER_${strings.length - 1}___`;
                    });

                    // Protect Variables (PHP only) using placeholders BEFORE keywords are run
                    const variables = [];
                    if (lang === "php") {
                        escapedLine = escapedLine.replace(/(\$[a-zA-Z0-9_]+)\b/g, (match) => {
                            variables.push(`<span class="token-variable">${match}</span>`);
                            return `___VAR_PLACEHOLDER_${variables.length - 1}___`;
                        });

                        escapedLine = escapedLine.replace(/(&lt;\?php|\?&gt;)/g, (match) => {
                            return `<span class="token-tag">${match}</span>`;
                        });
                    }

                    // Highlight Keywords safely
                    if (languageKeywords[lang]) {
                        escapedLine = escapedLine.replace(languageKeywords[lang], (match) => {
                            return `<span class="token-keyword">${match}</span>`;
                        });
                    }

                    // Restore protected variables (PHP only)
                    if (lang === "php") {
                        variables.forEach((varHtml, i) => {
                            escapedLine = escapedLine.replace(`___VAR_PLACEHOLDER_${i}___`, varHtml);
                        });
                    }

                    // Restore protected strings
                    strings.forEach((strHtml, i) => {
                        escapedLine = escapedLine.replace(`___STR_PLACEHOLDER_${i}___`, strHtml);
                    });

                    escapedLine += commentPart;
                }
            }

            return `<span class="line${isHighlighted}">${escapedLine || ' '}</span>`;
        }).join("");

        // Create and append the dynamic Copy Button directly to the <pre> block
        const copyBtn = document.createElement("button");
        copyBtn.textContent = "Copy";
        copyBtn.className = "copy-button";
        preBlock.appendChild(copyBtn);

        let isCopying = false;
        copyBtn.addEventListener("click", () => {
            if (isCopying) return;
            isCopying = true;
            const textToCopy = lines.join("\r\n");

            navigator.clipboard.writeText(textToCopy).then(() => {
                copyBtn.textContent = "Copied!";
                setTimeout(() => {
                    copyBtn.textContent = "Copy";
                    isCopying = false; // Unlock button after text resets
                }, 2000);
            }).catch(() => {
                isCopying = false; // Reset if copying fails
            });
        });
    });
});
