/// <reference path="../typings/react/react.d.ts" />
/// <reference path="../typings/react/react-dom.d.ts" />

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import {StoriesList} from "./StoriesList";

ReactDOM.render(React.createElement(StoriesList), document.querySelector("backlog"));
