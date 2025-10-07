import axios from 'axios'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import { useAuthStore } from '../store/modules/auth'
import 'sweetalert2/dist/sweetalert2.min.css';
import { globalRouter } from "../router/globalRouter";


axios.defaults.baseURL = '/api';


// // Request interceptor
axios.interceptors.request.use(request => {

  
  const token = useAuthStore().token;
  
  if (token) {
    request.headers.Authorization = `Bearer ${token}`
  }
  // request.headers['X-Socket-Id'] = Echo.socketId()

  return request
})

// Response interceptor
axios.interceptors.response.use(response => response, error => {

  
  const  status  = error.response?.status ?? error.status;

  const store = useAuthStore();
  
  if (status === 401) {
    store.logout();
    globalRouter.router?.push({ name: 'login' })
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

    Swal.fire({
      icon: 'error',
      title: 'An error has occurred.',
      text: response.data.message,
      reverseButtons: true,
      confirmButtonText: 'ok',
      cancelButtonText: 'cancel'
    })
  }
}


export default axios;