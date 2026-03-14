import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { Layout } from './components/Layout';
import { MarkdownPage } from './components/MarkdownPage';

function App() {
  return (
    <Router>
      <Layout>
        <Routes>
          <Route path="/*" element={<MarkdownPage />} />
        </Routes>
      </Layout>
    </Router>
  );
}

export default App;
