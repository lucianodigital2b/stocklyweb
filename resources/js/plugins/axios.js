import axios from 'axios'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { useAuthStore } from '../store/modules/auth'
import 'sweetalert2/dist/sweetalert2.min.css';
import { globalRouter } from "../router/globalRouter";


// Set base URL to empty string to use relative URLs
axios.defaults.baseURL = '';

// Add headers for Laravel
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';


// // Request interceptor
axios.interceptors.request.use(request => {

  
  const token = useAuthStore().token;
  
  console.log('token', token)
  if (token) {
    request.headers.Authorization = `Bearer ${token}`
  }
  // request.headers['X-Socket-Id'] = Echo.socketId()

  return request
})

// Response interceptor
axios.interceptors.response.use(response => {
  // Check if response contains Laravel dd() output (HTML content)
  if (response.headers['content-type']?.includes('text/html') && 
      typeof response.data === 'string' && (response.data.includes('sf-dump') || response.data.includes('dd('))) {
    
    // Create iframe to display dd() output
    const iframe = document.createElement('iframe')
    iframe.srcdoc = response.data
    iframe.style.width = '100%'
    iframe.style.height = '100%'
    
    Swal.fire({
      title: 'Laravel Debug Output',
      html: iframe.outerHTML,
      showConfirmButton: true,
      confirmButtonText: 'Close',
      customClass: { container: 'laravel-debug-modal' },
      grow: 'fullscreen',
      padding: 0
    })
  }
  
  return response
}, error => {

  
  const  status  = error.response?.status ?? error.status;
  const responseData = error.response?.data;

  const store = useAuthStore();
  
  // Check for 401 status OR "Unauthenticated." message
  if (status === 401 || (responseData && responseData.message === "Unauthenticated.")) {
    console.log('Authentication error detected, logging out and redirecting...');
    store.logout();
    
    if (globalRouter.router) {
      globalRouter.router.push({ name: 'login' });
    } else {
      console.error('Router not available in globalRouter');
      // Fallback to window location
      window.location.href = '/login';
    }
  }


  if (status >= 403) {
    serverError(error.response)
  }

  return Promise.reject(error)
})

let serverErrorModalShown = false
async function serverError (response) {
  if (serverErrorModalShown) {
    return
  }

  if ((response.headers['content-type'] || '').includes('text/html')) {
    const iframe = document.createElement('iframe')

    if (response.data instanceof Blob) {
      iframe.srcdoc = await response.data.text()
    } else {
      iframe.srcdoc = response.data
    }

    Swal.fire({
      html: iframe.outerHTML,
      showConfirmButton: false,
      customClass: { container: 'server-error-modal' },
      didDestroy: () => { serverErrorModalShown = false },
      grow: 'fullscreen',
      padding: 0
    })

    serverErrorModalShown = true
  } else {

    // Swal.fire({
    //   icon: 'error',
    //   title: 'An error has occurred.',
    //   text: response.data.message,
    //   reverseButtons: true,
    //   confirmButtonText: 'ok',
    //   cancelButtonText: 'cancel'
    // })
  }
}


export default axios;