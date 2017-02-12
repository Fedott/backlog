import React from 'react';
import ReactDOM from 'react-dom';
import { Router, Route, IndexRoute, browserHistory } from 'react-router'
import injectTapEventPlugin from 'react-tap-event-plugin';

injectTapEventPlugin();

import Application from './components/Application/Application.jsx';
import StoriesPage from './components/Story/StoriesPage.jsx';
import ProjectsPage from './components/Project/ProjectsPage.jsx';

require("../node_modules/material-design-lite/material.min.css");
require("../node_modules/material-design-lite/dist/material.indigo-pink.min.css");
require("../node_modules/material-design-lite/material.min.js");
require("./style.css");

ReactDOM.render((
    <Router history={browserHistory}>
        <Route path="/" component={Application}>
            <IndexRoute component={ProjectsPage} />
            <Route path="projects" component={ProjectsPage} />
            <Route path="project/:projectId/stories(/:filter)" component={StoriesPage} />
        </Route>
    </Router>
), document.getElementById('application'));
