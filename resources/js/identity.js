import { startApplication } from './bootstrap/app.js';
import { initializeIdentityNavigation } from './modules/identity-navigation.js';
import { initializeManagedEvents } from './modules/managed-events.js';
import { initializePublicFeed } from './modules/public-feed.js';
import { initializeRanking } from './modules/ranking.js';
import { initializeApplication, initializeApplicationHistory } from './modules/application.js';
import { initializeCertificates } from './modules/certificates.js';
import { initializeEventModeration } from './modules/event-moderation.js';
import { initializeNotifications } from './modules/notifications.js';
import { initializeOrganizerLinks } from './modules/organizer-links.js';
import { initializeParticipation } from './modules/participation.js';
import { initializePrivacyRequests } from './modules/privacy-requests.js';
import { initializePessoaRegistration } from './modules/pessoa-registration.js';
import './navigation-accessibility.js';
import '../css/components/accessibility.css';

startApplication('identity', () => {
    initializeIdentityNavigation();
    initializePublicFeed();
    initializeManagedEvents();
    initializeRanking();
    initializeApplication();
    initializeApplicationHistory();
    initializeCertificates();
    initializeEventModeration();
    initializeNotifications();
    initializeOrganizerLinks();
    initializeParticipation();
    initializePrivacyRequests();
    initializePessoaRegistration();
});

// Conceal a cached document until the server has revalidated the current session.
window.addEventListener('pagehide', event => {
    if (event.persisted) document.body.style.visibility = 'hidden';
});
window.addEventListener('pageshow', event => {
    if (event.persisted) location.reload();
});
