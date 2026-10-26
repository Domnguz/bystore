import './bootstrap';
import './bytestore';
import React from 'react';
import './ubicacion.js';
import './registro.js';
function App() {
    return (
        <div>
            React funcionando 🚀
        </div>
    );
}

const root = document.getElementById('react-app');

if (root) {
    import('react-dom/client').then(({ createRoot }) => {
        createRoot(root).render(<App />);
    });
}
