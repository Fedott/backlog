import React from 'react';
import ReactDOM from 'react-dom';
import { Router, Route, IndexRoute, browserHistory, hashHistory } from 'react-router'

import Application from './components/Application/Application.jsx';
import StoriesPage from './components/Story/StoriesPage.jsx';
import ProjectsPage from './components/Project/ProjectsPage.jsx';

require("../node_modules/material-design-lite/material.min.css");
require("../node_modules/material-design-lite/dist/material.indigo-pink.min.css");
require("../node_modules/material-design-lite/material.min.js");
require("./style.css");

ReactDOM.render((
    <Router history={hashHistory}>
        <Route path="/" component={Application}>
            <IndexRoute component={ProjectsPage} />
            <Route path="projects" component={ProjectsPage} />
            <Route path="stories/:filter" component={StoriesPage} />
        </Route>
    </Router>
), document.getElementById('application'));
