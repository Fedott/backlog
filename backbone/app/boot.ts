/// <reference path="../typings/react/react.d.ts" />
/// <reference path="../typings/react/react-dom.d.ts" />

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import {Application} from "./Application";

ReactDOM.render(React.createElement(Application), document.getElementById("container"));
