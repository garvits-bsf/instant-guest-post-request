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
  __experimentalText as Text,
  __experimentalHStack as HStack,
  __experimentalVStack as VStack,
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
      <div className="flex items-center justify-center min-h-[400px]">
        <Spinner className="w-8 h-8 border-4 border-blue-500 border-t-transparent" />
      </div>
    );
  }

  return (
    <div className="max-w-6xl mx-auto">
      <Card className="shadow-lg border border-gray-200">
        <CardHeader className="bg-gradient-to-r from-blue-700 to-blue-800 text-white">
          <Heading level={2} className="text-xl font-semibold">{__('Email Logs')}</Heading>
          <Text className="mt-1 text-sm text-blue-50">{__('Track all emails sent by the plugin')}</Text>
        </CardHeader>

        <CardBody className="p-0">
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th scope="col" className="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    {__('Recipient')}
                  </th>
                  <th scope="col" className="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    {__('Subject')}
                  </th>
                  <th scope="col" className="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    {__('Status')}
                  </th>
                  <th scope="col" className="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    {__('Date')}
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {logs.length > 0 ? (
                  logs.map((log) => (
                    <tr key={log.id} className="hover:bg-gray-50 transition-colors duration-150">
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm font-medium text-gray-900">{log.to_email}</div>
                      </td>
                      <td className="px-6 py-4">
                        <div className="text-sm text-gray-900 line-clamp-2">{log.subject}</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                          ${log.status === 'sent' 
                            ? 'bg-green-100 text-green-800 ring-1 ring-green-600/20' 
                            : 'bg-red-100 text-red-800 ring-1 ring-red-600/20'}`}>
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
                    <td colSpan="4" className="px-6 py-8 text-center">
                      <VStack spacing={2}>
                        <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <Text className="text-sm text-gray-500">{__('No email logs found')}</Text>
                      </VStack>
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </CardBody>

        {totalPages > 1 && (
          <CardFooter className="flex items-center justify-between bg-gray-50 px-6 py-4 border-t border-gray-200">
            <HStack spacing={4} className="w-full justify-between">
              <Button
                onClick={() => setPage(Math.max(1, page - 1))}
                disabled={page === 1}
                className="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
              >
                {__('Previous')}
              </Button>
              <Text className="text-sm text-gray-500">
                {__('Page')} {page} {__('of')} {totalPages}
              </Text>
              <Button
                onClick={() => setPage(Math.min(totalPages, page + 1))}
                disabled={page === totalPages}
                className="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
              >
                {__('Next')}
              </Button>
            </HStack>
          </CardFooter>
        )}
      </Card>
    </div>
  );
};

export default EmailLogs;