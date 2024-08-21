body {
  --primary-color: #48e;
  --gap-1: 0.1em;
  --gap-2: 0.2em;
  --gap-3: 0.5em;
}

body, html {
    margin: 0;
    padding: 0;
    background: #eee;
    font-family: monospace;
}

html.d-flex, html.d-flex > body  {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
}

.box {
    margin: 30px;
    background: #fff;
    border-radius: 3px;
    padding: 20px 30px;
    max-width: 600px;
    flex-grow: 1;
    font-size: 14px;
    color: #333;
    margin: 0 auto; /* if there's no flex */
    box-shadow: 0 2px 6px rgba(0,0,0,.1);
}

h1 {
    color: #111;
    font-size: 20px;
    /* border-bottom: 2px solid #08e; */
    padding: 0 0 0 0;
    margin: 10px 0 8px 0;
}

ul {
    padding: 0 0 0 10px;
}

li {
    list-style-type: none;
}

li::before {
    content: '- ';
}

button {
    box-sizing: content-box;
    display: block;
    background: var(--primary-color);
    color: #fff;
    border-radius: 0 0 2px 2px;
    border: 0;
    padding: 15px 30px;
    font-weight: bold;
    font-family: monospace;
    box-shadow: 0 2px 4px rgba(0,0,0,.2) inset;
}

button:hover {
    background: #37d;
}

button:focus {
    outline: 0;
}

button:focus:not(:hover) {
    outline: 0;
    border: 2px solid #25d;
    padding: 13px 28px;
}

button:active {
    outline: 0;
    box-shadow: 0 0 6px rgba(0,0,0,.3) inset;
    border: 0;
    padding: 15px 30px;
}

.d-flex {
    display: flex;
}

.flex-column {
    flex-direction: column;
}

.error {
    text-color: #d00;
}

.mb-1 {
    margin-bottom: var(--gap-1);
}
.mb-2 {
    margin-bottom: var(--gap-2);
}
.mb-3 {
    margin-bottom: var(--gap-3);
}

.mr-1 {
    margin-right: var(--gap-1);
}
.mr-2 {
    margin-right: var(--gap-2);
}
.mr-3 {
    margin-right: var(--gap-3);
}
