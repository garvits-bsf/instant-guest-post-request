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
  __experimentalHeading as Heading,
  __experimentalSpacer as Spacer,
  __experimentalDivider as Divider,
  __experimentalText as Text,
  __experimentalHStack as HStack,
  __experimentalVStack as VStack
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
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="animate-spin h-8 w-8 border-4 border-blue-500 rounded-full border-t-transparent"></div>
      </div>
    );
  }

  const tabs = [
    {
      name: 'general',
      title: __('General'),
      className: 'tab-general',
      content: (
        <VStack spacing={6}>
          <Card className="w-full border border-gray-200">
            <CardBody>
              <CheckboxControl
                label={__('Allow users to upload a featured image with their submission')}
                checked={settings.enable_featured_image}
                onChange={(value) => handleChange('enable_featured_image', value)}
                className="mb-4"
              />
              
              <RangeControl
                label={__('Max Upload Size (MB)')}
                value={settings.max_upload_size}
                onChange={(value) => handleChange('max_upload_size', value)}
                min={1}
                max={10}
                help={__('Maximum file size for featured image uploads (1-10 MB)')}
              />
            </CardBody>
          </Card>
        </VStack>
      ),
    },
    {
      name: 'email',
      title: __('Email Notifications'),
      className: 'tab-email',
      content: (
        <VStack spacing={8}>
          <Card className="w-full">
            <CardHeader>
              <Heading level={3} className="text-lg font-medium text-gray-900">{__('Admin Notifications')}</Heading>
            </CardHeader>
            <CardBody>
              <VStack spacing={4}>
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
              </VStack>
            </CardBody>
          </Card>
          
          <Card className="w-full">
            <CardHeader>
              <Heading level={3} className="text-lg font-medium text-gray-900">{__('Author Notifications')}</Heading>
            </CardHeader>
            <CardBody>
              <VStack spacing={6}>
                <CheckboxControl
                  label={__('Send email notifications to authors')}
                  checked={settings.enable_author_notifications}
                  onChange={(value) => handleChange('enable_author_notifications', value)}
                />
                
                {settings.enable_author_notifications && (
                  <>
                    <div className="space-y-4">
                      <Heading level={4} className="text-base font-medium text-gray-900">{__('Submission Confirmation')}</Heading>
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
                    </div>
                    
                    <Divider />
                    
                    <div className="space-y-4">
                      <Heading level={4} className="text-base font-medium text-gray-900">{__('Approval Notification')}</Heading>
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
                    </div>
                    
                    <Divider />
                    
                    <div className="space-y-4">
                      <Heading level={4} className="text-base font-medium text-gray-900">{__('Rejection Notification')}</Heading>
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
                    </div>
                  </>
                )}
              </VStack>
            </CardBody>
          </Card>
        </VStack>
      ),
    },
    {
      name: 'form',
      title: __('Form Settings'),
      className: 'tab-form',
      content: (
        <VStack spacing={6}>
          <Card className="w-full">
            <CardBody>
              <VStack spacing={6}>
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
                  <Text className="block text-sm font-medium text-gray-700 mb-2">{__('Required Fields')}</Text>
                  <VStack spacing={2}>
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
                  </VStack>
                </div>
              </VStack>
            </CardBody>
          </Card>
        </VStack>
      ),
    },
  ];

  return (
    <div className="max-w-4xl mx-auto">
      <Card className="shadow-lg border border-gray-200">
        <CardHeader className="bg-gradient-to-r from-blue-700 to-blue-800 text-white">
          <Heading level={2} className="text-xl font-semibold">{__('Guest Post Request Settings')}</Heading>
          <Text className="mt-1 text-sm text-blue-50">{__('Configure how guest post submissions are handled')}</Text>
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
            <div className="border-b border-gray-200">
              <TabPanel
                className="igpr-settings-tabs"
                activeClass="active-tab"
                tabs={tabs}
                tabClassName="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                activeTabClassName="border-b-2 border-blue-600 text-blue-700 font-semibold"
              >
                {(tab) => (
                  <div className="p-6">
                    {tab.content}
                  </div>
                )}
              </TabPanel>
            </div>
            
            <CardFooter className="flex justify-end bg-gray-50 px-6 py-4 border-t border-gray-200">
              <Button 
                isPrimary
                isBusy={saving}
                type="submit"
                className="px-6 py-2 bg-gradient-to-r from-blue-700 to-blue-800 hover:from-blue-800 hover:to-blue-900 text-white font-medium transition-all duration-200 shadow-sm hover:shadow-md"
              >
                {saving ? __('Saving...') : __('Save Settings')}
              </Button>
            </CardFooter>
          </form>
        </CardBody>
      </Card>

      <style>
        {`
          .igpr-settings-tabs .components-tab-panel__tabs {
            display: flex;
            gap: 1rem;
            padding: 0 1.5rem;
            margin: 0;
            border-bottom: 1px solid #e5e7eb;
          }
          
          .igpr-settings-tabs .components-tab-panel__tabs-item {
            padding: 0.75rem 1rem;
            margin: 0;
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #4b5563;
            font-weight: 500;
            transition: all 0.2s ease;
          }
          
          .igpr-settings-tabs .components-tab-panel__tabs-item:hover {
            color: #1f2937;
            border-bottom-color: #d1d5db;
          }
          
          .igpr-settings-tabs .components-tab-panel__tabs-item.active-tab {
            color: #1d4ed8;
            border-bottom-color: #2563eb;
            font-weight: 600;
          }
          
          .igpr-settings-tabs .components-tab-panel__tabs-item:focus {
            outline: none;
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #3b82f6;
            border-radius: 0.375rem;
          }
          
          .igpr-settings-tabs .components-tab-panel__tab-content {
            padding: 1.5rem;
          }
        `}
      </style>
    </div>
  );
};

export default Settings;
