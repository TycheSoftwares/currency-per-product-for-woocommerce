import apiFetch from '@wordpress/api-fetch';

const resetTracking = async () => {
    const response = await apiFetch({ path: `/cpp/v1/reset-tracking`, method: 'POST' });
    return response;
};

export default resetTracking;
