from docx import Document
from htmldocx import HtmlToDocx
import sys
import os


def export_to_word(html_contents, output_file='output.docx'):
    document = Document()
    new_parser = HtmlToDocx()
    new_parser.add_html_to_document(html_contents, document)
    document.save('test2.docx')
    print(f"Word document saved as {output_file}")

if __name__ == "__main__":
    os.chdir("D:/xampp/htdocs")
    html_contents = ' '.join(sys.argv[1:])
    print('wao')
    export_to_word(html_contents)
