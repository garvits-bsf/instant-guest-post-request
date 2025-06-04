import React, { useState } from 'react';

const tabs = [
  'General',
  'Form',
  'Notifications',
  'Spam Protection',
  'Integrations',
  'Advanced',
  'Help',
];

const SettingsPage = () => {
  const [active, setActive] = useState('General');
  return (
    <div className="p-6">
      <div className="border-b mb-4">
        <nav className="-mb-px flex space-x-4">
          {tabs.map((tab) => (
            <button
              key={tab}
              onClick={() => setActive(tab)}
              className={`py-2 px-4 border-b-2 ${
                active === tab
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700'
              }`}
            >
              {tab}
            </button>
          ))}
        </nav>
      </div>

      {active === 'General' && (
        <div className="space-y-4">
          <div>
            <label htmlFor="senderEmail" className="block font-medium">Sender Email</label>
            <input id="senderEmail" type="text" className="mt-1 block w-full border rounded p-2" />
          </div>
          <div className="flex items-center space-x-2">
            <input id="enableModeration" type="checkbox" className="rounded" />
            <label htmlFor="enableModeration">Enable Moderation</label>
          </div>
        </div>
      )}

      {active === 'Form' && (
        <div className="space-y-4">
          <div>
            <label htmlFor="titleLabel" className="block font-medium">Title Label</label>
            <input id="titleLabel" type="text" className="mt-1 block w-full border rounded p-2" />
          </div>
          <div>
            <label htmlFor="instructions" className="block font-medium">Instructions</label>
            <textarea id="instructions" className="mt-1 block w-full border rounded p-2" rows="4"></textarea>
          </div>
        </div>
      )}

      {active === 'Notifications' && (
        <div className="space-y-4">
          <div>
            <label htmlFor="adminEmail" className="block font-medium">Admin Email</label>
            <input id="adminEmail" type="email" className="mt-1 block w-full border rounded p-2" />
          </div>
          <div className="flex items-center space-x-2">
            <input id="sendConfirmation" type="checkbox" className="rounded" />
            <label htmlFor="sendConfirmation">Send Confirmation</label>
          </div>
        </div>
      )}

      {active === 'Spam Protection' && (
        <div className="space-y-4">
          <div className="flex items-center space-x-2">
            <input id="enableRecaptcha" type="checkbox" className="rounded" />
            <label htmlFor="enableRecaptcha">Enable reCAPTCHA</label>
          </div>
        </div>
      )}

      {active === 'Integrations' && (
        <div className="space-y-4">
          <div>
            <label htmlFor="webhookUrl" className="block font-medium">Webhook URL</label>
            <input id="webhookUrl" type="url" className="mt-1 block w-full border rounded p-2" />
          </div>
        </div>
      )}

      {active === 'Advanced' && (
        <div className="space-y-4">
          <div className="flex items-center space-x-2">
            <input id="debugMode" type="checkbox" className="rounded" />
            <label htmlFor="debugMode">Debug Mode</label>
          </div>
        </div>
      )}

      {active === 'Help' && (
        <p>
          For help, visit <a href="https://example.com" className="text-blue-600 hover:underline">documentation</a>.
        </p>
      )}

      <div className="mt-4">
        <button className="bg-blue-600 text-white px-4 py-2 rounded">Save Changes</button>
      </div>
    </div>
  );
};

export default SettingsPage;
