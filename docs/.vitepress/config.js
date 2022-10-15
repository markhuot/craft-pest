import { getSidebar } from 'vitepress-plugin-auto-sidebar'
import fs from 'fs';
import path from 'path';

const getSidebarItems = (dir, currentRoot, root, options) => {
	return dir.filter(e => e.endsWith('.md') || fs.statSync(path.resolve(currentRoot, e)).isDirectory()).map((e) => {
    const childDir = path.resolve(currentRoot, e);
    if (path.basename(childDir).substr(0,1) != '.' && fs.statSync(childDir).isDirectory() && options.showGroups) {
      return {
        text: (e.charAt(0).toUpperCase() + e.slice(1)).replaceAll('-', ' '),
        collapsible: options.collapsible,
        collapsed: options.collapsed,
        items: getSidebarItems(fs.readdirSync(childDir), childDir, root, {...options, showMd: true, showGroups: true}, true)
      };
    } else if (options.showMd && e.endsWith('.md')) {
      return {
        text: ((e.charAt(0).toUpperCase() + e.slice(1)).slice(0, -3)).replaceAll('-', ' '),
        link: '/' + childDir.replace(root, '').replace(/\.md$/, '.html')
      };
    }
    return null;
  }).filter(Boolean)
};

const docsPath = path.join(process.cwd(), 'docs');
const sidebar = [
  {
    text: 'Getting Started',
    items: [
      ...getSidebarItems(fs.readdirSync(docsPath), docsPath, docsPath, { contentDirs: null, collapsible: false, collapsed: false, showMd: true, showGroups: false }),
    ],
  },
  ...getSidebarItems(fs.readdirSync(docsPath), docsPath, docsPath, { contentDirs: null, collapsible: false, collapsed: false, showMd: false, showGroups: true }),
]
export default {
  title: 'Craft Pest',
  themeConfig: {
    // sidebar: getSidebar({ contentRoot: '/docs/', contentDirs: null, collapsible: false, collapsed: false })
    //sidebar: getSidebarItems(fs.readdirSync(docsPath), docsPath, docsPath, { contentDirs: null, collapsible: false, collapsed: false })
    sidebar,
  }
}
