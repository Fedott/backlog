import React from 'react';
import ReactDOM from 'react-dom';
import { Router, Route, IndexRoute, browserHistory } from 'react-router'

import Application from './components/Application/Application.jsx';
import StoriesPage from './components/Story/StoriesPage.jsx';

require("../node_modules/material-design-lite/material.min.css");
require("../node_modules/material-design-lite/dist/material.indigo-pink.min.css");
require("../node_modules/material-design-lite/material.min.js");
require("./style.css");

ReactDOM.render((
    <Router history={browserHistory}>
        <Route path="/" component={Application}>
            <IndexRoute component={StoriesPage} />
            <Route path="stories/all" component={StoriesPage} />
            <Route path="stories/completed" component={StoriesPage} />
        </Route>
    </Router>
), document.getElementById('application'));
