import json
from urllib.request import urlopen
from bs4 import BeautifulSoup

MAIN_URL = 'http://www.dns-shop.ru'

dataPath = [
	{
		'url': 'http://www.dns-shop.ru/catalog/3660/materinskie-platy/',
		'folder': 'motherboard/'
	},
	{
		'url': 'http://www.dns-shop.ru/catalog/3659/processory/',
		'folder': 'cpu/'
	},
	{
		'url': 'http://www.dns-shop.ru/catalog/3663/videokarty/',
		'folder': 'video/'
	},
	{
		'url': 'http://www.dns-shop.ru/catalog/3661/operativnaya-pamyat/',
		'folder': 'ram/'
	},
	{
		'url': 'http://www.dns-shop.ru/catalog/5108/sistemy-oxlazhdeniya-processorov/',
		'folder': 'culling/'
	},
	{
		'url': 'http://www.dns-shop.ru/catalog/3619/zhestkie-diski-35/',
		'folder': 'hdd/'
	},
	{
		'url': 'http://www.dns-shop.ru/catalog/3670/bloki-pitaniya/',
		'folder': 'psu/'
	},
	{
		'url': 'http://www.dns-shop.ru/catalog/3671/korpusa-dlya-kompyuterov/',
		'folder': 'hull/'
	}
]


def main():
	for item in dataPath:
		soup = BeautifulSoup(get_html(item['url']))
		table = soup.find('li', class_='filter control_3')
		a = table.find_all('a')
		for x in a:
			print(x['href'] + ' | ' + x.text)
			listLinks = get_pages(get_html(item['url'] + x['href']))
			nLinks = len(listLinks)
			allVideoCards = [];
			for i in range(0, nLinks):
				html = get_html(listLinks[i])
				videoAttr = parse(html)
				videoAttr['Ссылка'] = listLinks[i]
				allVideoCards.append(videoAttr)
				print('Парсинг: %d%%' % (i / nLinks * 100))

			jsStr = json.dumps(allVideoCards)
			f = open(item['folder'] + x.text + '.json', 'w')
			f.write(jsStr)
			f.close()

def get_html(url):
	response = urlopen(url)
	return response.read()

def get_pages(html): 
	'''
	Find links
	'''
	soup = BeautifulSoup(html)
	table = soup.find('div', class_='items-list node-block list')
	videocards = table.findAllNext('a', class_='show-popover ec-price-item-link')
	
	links = []
	
	for link in videocards:
		links.append(MAIN_URL + link['href'])
	return links


def parse(html):
	'''
	Find params
	'''
	soup = BeautifulSoup(html)
	price = soup.find('div', class_='price_g')
	table = soup.find('table', class_='table-params')

	lines = {}

	for tr in table.find_all('tr'):
		cols = tr.find_all('td')
		if len(cols) > 1:
			k = cols[0].text.strip().replace(u'\xa0', u' ')
			v = cols[1].text.strip().replace(u'\xa0', u' ')
			lines[k] = v

	lines['Цена'] = price.text.replace(u'\xa0', u' ')

	return lines

if __name__ == '__main__':
	main()