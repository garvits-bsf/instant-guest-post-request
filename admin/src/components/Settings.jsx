import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { 
  TabPanel, 
  CheckboxControl, 
  TextControl, 
  TextareaControl, 
  RangeControl, 
  Button, 
  Notice,
  Card,
  CardHeader,
  CardBody,
  CardFooter,
  __experimentalHeading as Heading
} from '@wordpress/components';

const Settings = () => {
  const [settings, setSettings] = useState({
    enable_admin_notifications: true,
    admin_email: '',
    admin_email_subject: '[{site_name}] New Guest Post Submission: {post_title}',
    enable_author_notifications: true,
    author_email_subject: '[{site_name}] Thank You for Your Guest Post Submission',
    author_email_template: '',
    approval_email_subject: '[{site_name}] Your Guest Post Has Been Approved',
    approval_email_template: '',
    rejection_email_subject: '[{site_name}] Your Guest Post Submission',
    rejection_email_template: '',
    form_title: 'Submit a Guest Post',
    success_message: 'Your guest post has been submitted successfully and is awaiting review.',
    required_fields: ['name', 'email', 'title', 'content'],
    enable_featured_image: true,
    max_upload_size: 2,
  });
  
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState({ type: '', text: '' });

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      setLoading(true);
      const response = await fetch(`${igprData.apiUrl}/settings`, {
        headers: {
          'X-WP-Nonce': igprData.nonce
        }
      });
      const data = await response.json();
      setSettings(data);
    } catch (error) {
      console.error('Error fetching settings:', error);
      setMessage({ type: 'error', text: __('Failed to load settings.') });
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (name, value) => {
    setSettings(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleRequiredFieldChange = (field, checked) => {
    setSettings(prev => {
      const newRequiredFields = checked 
        ? [...prev.required_fields, field]
        : prev.required_fields.filter(f => f !== field);
      
      return {
        ...prev,
        required_fields: newRequiredFields
      };
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      setSaving(true);
      const response = await fetch(`${igprData.apiUrl}/settings`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': igprData.nonce
        },
        body: JSON.stringify(settings)
      });
      
      if (response.ok) {
        setMessage({ type: 'success', text: __('Settings saved successfully.') });
        setTimeout(() => setMessage({ type: '', text: '' }), 3000);
      } else {
        setMessage({ type: 'error', text: __('Failed to save settings.') });
      }
    } catch (error) {
      console.error('Error saving settings:', error);
      setMessage({ type: 'error', text: __('An error occurred while saving settings.') });
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="p-8">
        <div className="flex justify-center">
          <div className="animate-spin h-8 w-8 border-4 border-blue-500 rounded-full border-t-transparent"></div>
        </div>
      </div>
    );
  }

  const tabs = [
    {
      name: 'general',
      title: __('General'),
      className: 'tab-general',
      content: (
        <div className="space-y-6">
          <CheckboxControl
            label={__('Allow users to upload a featured image with their submission')}
            checked={settings.enable_featured_image}
            onChange={(value) => handleChange('enable_featured_image', value)}
          />
          
          <RangeControl
            label={__('Max Upload Size (MB)')}
            value={settings.max_upload_size}
            onChange={(value) => handleChange('max_upload_size', value)}
            min={1}
            max={10}
            help={__('Maximum file size for featured image uploads (1-10 MB)')}
          />
        </div>
      ),
    },
    {
      name: 'email',
      title: __('Email Notifications'),
      className: 'tab-email',
      content: (
        <div className="space-y-8">
          <div>
            <Heading level={3} className="text-lg font-medium text-gray-900 mb-4">{__('Admin Notifications')}</Heading>
            <div className="mt-4 space-y-4">
              <CheckboxControl
                label={__('Send email notification to admin when a new guest post is submitted')}
                checked={settings.enable_admin_notifications}
                onChange={(value) => handleChange('enable_admin_notifications', value)}
              />
              
              {settings.enable_admin_notifications && (
                <>
                  <TextControl
                    label={__('Admin Email')}
                    value={settings.admin_email}
                    onChange={(value) => handleChange('admin_email', value)}
                    help={__('Leave blank to use the default admin email')}
                  />
                  
                  <TextControl
                    label={__('Admin Email Subject')}
                    value={settings.admin_email_subject}
                    onChange={(value) => handleChange('admin_email_subject', value)}
                    help={__('Available placeholders: {site_name}, {post_title}')}
                  />
                </>
              )}
            </div>
          </div>
          
          <div>
            <Heading level={3} className="text-lg font-medium text-gray-900 mb-4">{__('Author Notifications')}</Heading>
            <div className="mt-4 space-y-4">
              <CheckboxControl
                label={__('Send email notifications to authors')}
                checked={settings.enable_author_notifications}
                onChange={(value) => handleChange('enable_author_notifications', value)}
              />
              
              {settings.enable_author_notifications && (
                <>
                  <Heading level={4} className="text-base font-medium text-gray-900 mb-3">{__('Submission Confirmation')}</Heading>
                  <TextControl
                    label={__('Email Subject')}
                    value={settings.author_email_subject}
                    onChange={(value) => handleChange('author_email_subject', value)}
                    help={__('Available placeholders: {site_name}, {post_title}')}
                  />
                  <TextareaControl
                    label={__('Email Template')}
                    value={settings.author_email_template}
                    onChange={(value) => handleChange('author_email_template', value)}
                    help={__('Available placeholders: {author_name}, {post_title}, {site_name}')}
                    rows={6}
                  />
                  
                  <Heading level={4} className="text-base font-medium text-gray-900 mb-3">{__('Approval Notification')}</Heading>
                  <TextControl
                    label={__('Email Subject')}
                    value={settings.approval_email_subject}
                    onChange={(value) => handleChange('approval_email_subject', value)}
                    help={__('Available placeholders: {site_name}, {post_title}')}
                  />
                  <TextareaControl
                    label={__('Email Template')}
                    value={settings.approval_email_template}
                    onChange={(value) => handleChange('approval_email_template', value)}
                    help={__('Available placeholders: {author_name}, {post_title}, {site_name}, {post_url}')}
                    rows={6}
                  />
                  
                  <Heading level={4} className="text-base font-medium text-gray-900 mb-3">{__('Rejection Notification')}</Heading>
                  <TextControl
                    label={__('Email Subject')}
                    value={settings.rejection_email_subject}
                    onChange={(value) => handleChange('rejection_email_subject', value)}
                    help={__('Available placeholders: {site_name}, {post_title}')}
                  />
                  <TextareaControl
                    label={__('Email Template')}
                    value={settings.rejection_email_template}
                    onChange={(value) => handleChange('rejection_email_template', value)}
                    help={__('Available placeholders: {author_name}, {post_title}, {site_name}')}
                    rows={6}
                  />
                </>
              )}
            </div>
          </div>
        </div>
      ),
    },
    {
      name: 'form',
      title: __('Form Settings'),
      className: 'tab-form',
      content: (
        <div className="space-y-6">
          <TextControl
            label={__('Form Title')}
            value={settings.form_title}
            onChange={(value) => handleChange('form_title', value)}
          />
          
          <TextControl
            label={__('Success Message')}
            value={settings.success_message}
            onChange={(value) => handleChange('success_message', value)}
            help={__('Message displayed after successful submission')}
          />
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">{__('Required Fields')}</label>
            <div className="space-y-2">
              <CheckboxControl
                label={__('Name')}
                checked={settings.required_fields.includes('name')}
                onChange={(checked) => handleRequiredFieldChange('name', checked)}
              />
              <CheckboxControl
                label={__('Email')}
                checked={settings.required_fields.includes('email')}
                onChange={(checked) => handleRequiredFieldChange('email', checked)}
              />
              <CheckboxControl
                label={__('Post Title')}
                checked={settings.required_fields.includes('title')}
                onChange={(checked) => handleRequiredFieldChange('title', checked)}
              />
              <CheckboxControl
                label={__('Post Content')}
                checked={settings.required_fields.includes('content')}
                onChange={(checked) => handleRequiredFieldChange('content', checked)}
              />
            </div>
          </div>
        </div>
      ),
    },
  ];

  return (
    <Card className="max-w-4xl mx-auto">
      <CardHeader>
        <Heading level={2} className="text-xl font-semibold text-gray-900">{__('Guest Post Request Settings')}</Heading>
        <p className="mt-1 text-sm text-gray-500">{__('Configure how guest post submissions are handled')}</p>
      </CardHeader>
      
      <CardBody>
        {message.text && (
          <Notice 
            status={message.type === 'success' ? 'success' : 'error'}
            isDismissible={true}
            onRemove={() => setMessage({ type: '', text: '' })}
            className="mb-6"
          >
            {message.text}
          </Notice>
        )}
        
        <form onSubmit={handleSubmit}>
          <TabPanel
            className="igpr-settings-tabs"
            activeClass="active-tab"
            tabs={tabs}
          >
            {(tab) => (
              <div className="p-6">
                {tab.content}
              </div>
            )}
          </TabPanel>
          
          <CardFooter className="flex justify-end bg-gray-50 px-6 py-4">
            <Button 
              isPrimary
              isBusy={saving}
              type="submit"
              className="px-4 py-2"
            >
              {saving ? __('Saving...') : __('Save Settings')}
            </Button>
          </CardFooter>
        </form>
      </CardBody>
    </Card>
  );
};

export default Settings;
