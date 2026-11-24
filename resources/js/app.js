import { startApplication } from './bootstrap/app.js';
import { initializeDemoAdminControls } from './modules/demo-admin-controls.js';
import './notifications-demo.js';
import './dialog-accessibility.js';
import './navigation-accessibility.js';
import '../css/components/accessibility.css';
import './admin-demo.js';
import './organizer-demo.js';
import './events-demo.js';
import './account-demo.js';
import './participation-demo.js';
import './participation-certificates.js';
import './participation-reliability.js';

import './privacy-demo.js';

startApplication('demo', initializeDemoAdminControls);
