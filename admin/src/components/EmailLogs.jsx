import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import {
  Button,
  Card,
  CardHeader,
  CardBody,
  CardFooter,
  Spinner,
  __experimentalHeading as Heading,
} from '@wordpress/components';

const EmailLogs = () => {
  const [logs, setLogs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  useEffect(() => {
    fetchLogs();
  }, [page]);

  const fetchLogs = async () => {
    try {
      setLoading(true);
      const response = await fetch(`${igprData.apiUrl}/email-logs?page=${page}`, {
        headers: {
          'X-WP-Nonce': igprData.nonce
        }
      });
      const data = await response.json();
      setLogs(data.logs);
      setTotalPages(data.totalPages);
    } catch (error) {
      console.error('Error fetching email logs:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center p-8">
        <Spinner />
      </div>
    );
  }

  return (
    <Card className="overflow-hidden">
      <CardHeader>
        <Heading level={3} className="text-lg font-medium text-gray-900">
          {__('Email Logs')}
        </Heading>
        <p className="mt-1 text-sm text-gray-500">
          {__('Track all emails sent by the plugin')}
        </p>
      </CardHeader>

      <CardBody className="p-0">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{__('Recipient')}</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{__('Subject')}</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{__('Status')}</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{__('Date')}</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {logs.length > 0 ? (
              logs.map((log) => (
                <tr key={log.id}>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm text-gray-900">{log.to_email}</div>
                  </td>
                  <td className="px-6 py-4">
                    <div className="text-sm text-gray-900">{log.subject}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                      ${log.status === 'sent' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                      {log.status}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm text-gray-500">{log.created_at}</div>
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan="4" className="px-6 py-4 text-center text-sm text-gray-500">
                  {__('No email logs found')}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
      </CardBody>

      {totalPages > 1 && (
        <CardFooter className="flex items-center justify-between">
          <div className="flex-1 flex justify-between">
            <Button
              onClick={() => setPage(Math.max(1, page - 1))}
              disabled={page === 1}
            >
              {__('Previous')}
            </Button>
            <Button
              onClick={() => setPage(Math.min(totalPages, page + 1))}
              disabled={page === totalPages}
            >
              {__('Next')}
            </Button>
          </div>
        </CardFooter>
      )}
    </Card>
  );
};

export default EmailLogs;