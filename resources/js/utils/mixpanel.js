// Mixpanel Analytics Utility
import mixpanel from "mixpanel-browser";

/**
 * Identify a user in Mixpanel
 * @param {string} userId - Unique user identifier
 * @param {object} userProperties - User properties (name, email, etc.)
 */
export function identifyUser(userId, userProperties = {}) {
  if (!userId) return;
  
  mixpanel.identify(userId);
  
  if (Object.keys(userProperties).length > 0) {
    mixpanel.people.set(userProperties);
  }
}

/**
 * Track a Mixpanel event
 * @param {string} eventName - Name of the event
 * @param {object} properties - Event properties
 */
export function trackEvent(eventName, properties = {}) {
  mixpanel.track(eventName, properties);
}

/**
 * Track page view
 */
export function trackPageView() {
  const pageUrl = window.location.href;
  const pageTitle = document.title;
  const userId = window.mixpanelUser?.id || null;
  
  trackEvent("Page View", {
    page_url: pageUrl,
    page_title: pageTitle,
    user_id: userId,
  });
}


