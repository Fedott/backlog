import injectTapEventPlugin from 'react-tap-event-plugin';
injectTapEventPlugin();

import React from 'react';
import ReactDOM from 'react-dom';
import { Router, Route, IndexRoute, browserHistory } from 'react-router'

import Application from './components/Application/Application.jsx';
import StoriesPage from './components/Story/StoriesPage.jsx';
import ProjectsPage from './components/Project/ProjectsPage.jsx';

ReactDOM.render((
    <Router history={browserHistory}>
        <Route path="/" component={Application}>
            <IndexRoute component={ProjectsPage} />
            <Route path="projects" component={ProjectsPage} />
            <Route path="project/:projectId/stories(/:filter)" component={StoriesPage} />
        </Route>
    </Router>
), document.getElementById('application'));
