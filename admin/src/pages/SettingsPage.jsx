import React from 'react';
import { Tabs, TabPanel, Input, Toggle, TextArea, Button } from '@brainstormforce/force-ui';

const SettingsPage = () => (
  <div className="p-6">
    <Tabs>
      <TabPanel title="General">
        <div className="space-y-4">
          <Input label="Sender Email" id="senderEmail" />
          <Toggle label="Enable Moderation" id="enableModeration" />
        </div>
      </TabPanel>
      <TabPanel title="Form">
        <div className="space-y-4">
          <Input label="Title Label" id="titleLabel" />
          <TextArea label="Instructions" id="instructions" />
        </div>
      </TabPanel>
      <TabPanel title="Notifications">
        <div className="space-y-4">
          <Input label="Admin Email" id="adminEmail" />
          <Toggle label="Send Confirmation" id="sendConfirmation" />
        </div>
      </TabPanel>
      <TabPanel title="Spam Protection">
        <div className="space-y-4">
          <Toggle label="Enable reCAPTCHA" id="enableRecaptcha" />
        </div>
      </TabPanel>
      <TabPanel title="Integrations">
        <div className="space-y-4">
          <Input label="Webhook URL" id="webhookUrl" />
        </div>
      </TabPanel>
      <TabPanel title="Advanced">
        <div className="space-y-4">
          <Toggle label="Debug Mode" id="debugMode" />
        </div>
      </TabPanel>
      <TabPanel title="Help">
        <p>For help, visit <a href="https://example.com">documentation</a>.</p>
      </TabPanel>
    </Tabs>
    <div className="mt-4">
      <Button variant="primary">Save Changes</Button>
    </div>
  </div>
);

export default SettingsPage;
