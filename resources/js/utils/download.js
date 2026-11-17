import http from '../http';

/**
 * Downloads a file from an authenticated API route. A plain <a href> can't
 * carry the Sanctum Bearer token, so we fetch as a blob (the axios
 * interceptor attaches the token) and trigger the save via an object URL.
 */
export async function downloadFile(url, filename, mimeType) {
    const response = await http.get(url, { responseType: 'blob' });
    const blobUrl = URL.createObjectURL(new Blob([response.data], { type: mimeType }));

    const link = document.createElement('a');
    link.href = blobUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(blobUrl);
}

export async function downloadPdf(url, filename) {
    return downloadFile(url, filename, 'application/pdf');
}

export async function downloadExcel(url, filename) {
    return downloadFile(url, filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
}
