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

const SubmissionsTable = () => {
  const [submissions, setSubmissions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  useEffect(() => {
    fetchSubmissions();
  }, [page]);

  const fetchSubmissions = async () => {
    try {
      setLoading(true);
      const response = await fetch(`${igprData.apiUrl}/submissions?page=${page}`, {
        headers: {
          'X-WP-Nonce': igprData.nonce
        }
      });
      const data = await response.json();
      setSubmissions(data.submissions);
      setTotalPages(data.totalPages);
    } catch (error) {
      console.error('Error fetching submissions:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleApprove = async (id) => {
    if (confirm(__('Are you sure you want to approve this submission?'))) {
      try {
        const response = await fetch(`${igprData.apiUrl}/submissions/${id}/approve`, {
          method: 'POST',
          headers: {
            'X-WP-Nonce': igprData.nonce
          }
        });
        
        if (response.ok) {
          fetchSubmissions();
        }
      } catch (error) {
        console.error('Error approving submission:', error);
      }
    }
  };

  const handleReject = async (id) => {
    if (confirm(__('Are you sure you want to reject this submission?'))) {
      try {
        const response = await fetch(`${igprData.apiUrl}/submissions/${id}/reject`, {
          method: 'POST',
          headers: {
            'X-WP-Nonce': igprData.nonce
          }
        });
        
        if (response.ok) {
          fetchSubmissions();
        }
      } catch (error) {
        console.error('Error rejecting submission:', error);
      }
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
          <Heading level={2} className="text-xl font-semibold">{__('Guest Post Submissions')}</Heading>
          <Text className="mt-1 text-sm text-blue-50">{__('Manage guest post submissions')}</Text>
        </CardHeader>

        <CardBody className="p-0">
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th scope="col" className="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{__('Title')}</th>
                  <th scope="col" className="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{__('Author')}</th>
                  <th scope="col" className="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{__('Date')}</th>
                  <th scope="col" className="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{__('Status')}</th>
                  <th scope="col" className="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{__('Actions')}</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {submissions.length > 0 ? (
                  submissions.map((submission) => (
                    <tr key={submission.id} className="hover:bg-gray-50 transition-colors duration-150">
                      <td className="px-6 py-4">
                        <div className="text-sm font-medium text-gray-900">{submission.title}</div>
                      </td>
                      <td className="px-6 py-4">
                        <div className="text-sm font-medium text-gray-900">{submission.author_name}</div>
                        <div className="text-sm text-gray-500">{submission.author_email}</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm text-gray-500">{submission.date}</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                          ${submission.status === 'pending' 
                            ? 'bg-yellow-100 text-yellow-800 ring-1 ring-yellow-600/20' 
                            : submission.status === 'published' 
                            ? 'bg-green-100 text-green-800 ring-1 ring-green-600/20' 
                            : 'bg-red-100 text-red-800 ring-1 ring-red-600/20'}`}>
                          {submission.status}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <HStack spacing={2}>
                          <Button
                            isLink
                            href={submission.edit_url}
                            className="text-blue-600 hover:text-blue-800"
                          >
                            {__('View')}
                          </Button>
                          {submission.status === 'pending' && (
                            <>
                              <Button
                                isSecondary
                                onClick={() => handleApprove(submission.id)}
                                className="text-green-700 hover:text-green-800 bg-green-50 hover:bg-green-100"
                              >
                                {__('Approve')}
                              </Button>
                              <Button
                                isSecondary
                                onClick={() => handleReject(submission.id)}
                                className="text-red-700 hover:text-red-800 bg-red-50 hover:bg-red-100"
                              >
                                {__('Reject')}
                              </Button>
                            </>
                          )}
                        </HStack>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="5" className="px-6 py-8 text-center">
                      <VStack spacing={2}>
                        <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <Text className="text-sm text-gray-500">{__('No submissions found')}</Text>
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

export default SubmissionsTable;