<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        #cssportal-grid {
            display: grid;
            grid-template-rows: repeat(6, 1fr);
            grid-template-columns: repeat(6, 1fr);
            gap: 0;
            width: 100%;
            height: 100%;
        }

        #evnt {
            grid-area: 1 / 1 / 7 / 4;
            background-color: rgba(113, 203, 239, 0.5);
        }

        #div3 {
            grid-area: 1 / 4 / 4 / 7;
            background-color: rgba(154, 79, 185, 0.5);
        }

        #div4 {
            grid-area: 4 / 4 / 7 / 7;
            background-color: rgba(70, 210, 113, 0.5);
        }

        .custom-loader {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 8px solid #0000;
            border-right-color: #766DF497;
            position: relative;
            animation: s4 1s infinite linear;
        }

        .custom-loader:before,
        .custom-loader:after {
            content: "";
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: inherit;
            animation: inherit;
            animation-duration: 2s;
        }

        .custom-loader:after {
            animation-duration: 4s;
        }

        @keyframes s4 {
            100% {
                transform: rotate(1turn)
            }
        }

        /* HTML: <div class="ribbon">Your text content</div> */
        .ribbon {
            font-size: 28px;
            font-weight: bold;
            color: #fff;
        }

        .ribbon {
            --f: .5em;
            /* control the folded part */

            position: absolute;
            top: 0;
            right: 0;
            line-height: 1.8;
            padding-inline: 1lh;
            padding-bottom: var(--f);
            border-image: conic-gradient(#0008 0 0) 51%/var(--f);
            clip-path: polygon(100% calc(100% - var(--f)), 100% 100%, calc(100% - var(--f)) calc(100% - var(--f)), var(--f) calc(100% - var(--f)), 0 100%, 0 calc(100% - var(--f)), 999px calc(100% - var(--f) - 999px), calc(100% - 999px) calc(100% - var(--f) - 999px));
            transform: translate(calc((1 - cos(45deg))*100%), -100%) rotate(45deg);
            transform-origin: 0% 100%;
            background-color: #BD1550;
            /* the main color  */
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>

</head>

<body>
    <div class="custom-loader"></div>
    <div id="cssportal-grid">
        <div id="evnt">

        </div class="ribbon">
        <div id="div3">div3</div>
        <div id="div4">div4</div>
    </div>
    <div class="ribbon">
      new
    </div>

    

    <button class="copy-btn" data-clipboard-text="Text to copy">Copy</button>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var clipboard = new ClipboardJS('.copy-btn');

            clipboard.on('success', function (e) {
                var originalText = e.trigger.innerText;

                e.trigger.innerText = 'Copied!';

                setTimeout(function () {
                    e.trigger.innerText = originalText;
                }, 2000);

                e.clearSelection();
            });

            clipboard.on('error', function (e) {
                console.error('Action:', e.action);
                console.error('Trigger:', e.trigger);
            });
        });
    </script>

</body>

</html>