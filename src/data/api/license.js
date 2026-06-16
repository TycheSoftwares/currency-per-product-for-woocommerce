import apiFetch from '@wordpress/api-fetch';

export const getLicense = async () => {
    return apiFetch({ path: '/cpp/v1/license' });
};

export const updateLicense = async (payload) => {
    return apiFetch({
        path: '/cpp/v1/license',
        method: 'POST',
        data: payload,
    });
};
