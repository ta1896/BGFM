import React, { useState, useEffect } from 'react';
import ReactMarkdown from 'react-markdown';
import { useLocation } from 'react-router-dom';

// Dynamically import all markdown files in the content directory as raw text
const markdownFiles = import.meta.glob('../content/**/*.md', { query: '?raw', import: 'default' });

export function MarkdownPage() {
  const location = useLocation();
  const [content, setContent] = useState('');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    // Scroll to top on route change
    const mainScroll = document.getElementById('main-scroll');
    if (mainScroll) mainScroll.scrollTop = 0;

    const loadContent = async () => {
      setIsLoading(true);
      let path = location.pathname;
      if (path === '/') path = '/home';
      if (path.endsWith('/')) path = path.slice(0, -1);

      const filePath = `../content${path}.md`;

      if (markdownFiles[filePath]) {
        try {
          const rawMarkdown = await markdownFiles[filePath]();
          setContent(rawMarkdown);
        } catch (error) {
          setContent(`# Fehler\n\nFehler beim Laden der Seite.`);
        }
      } else {
        setContent(`# 404\n\nDie Seite unter \`${location.pathname}\` wurde nicht gefunden.`);
      }
      setIsLoading(false);
    };

    loadContent();
  }, [location.pathname]);

  if (isLoading) {
    return (
      <div className="flex animate-pulse flex-col space-y-4">
        <div className="h-8 w-2/3 rounded-lg bg-slate-800"></div>
        <div className="h-4 w-full rounded bg-slate-800/50"></div>
        <div className="h-4 w-5/6 rounded bg-slate-800/50"></div>
        <div className="h-4 w-4/6 rounded bg-slate-800/50"></div>
      </div>
    );
  }

  return (
    <div className="prose prose-invert max-w-none animate-in fade-in slide-in-from-bottom-4 duration-500 delay-100">
      <ReactMarkdown>{content}</ReactMarkdown>
    </div>
  );
}
